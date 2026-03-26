import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { Suggestion } from '@tiptap/suggestion';
import { common, createLowlight } from 'lowlight';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Highlight from '@tiptap/extension-highlight';
import Color from '@tiptap/extension-color';
import { TextStyle } from '@tiptap/extension-text-style';
import Superscript from '@tiptap/extension-superscript';
import Subscript from '@tiptap/extension-subscript';

// Solo los lenguajes más habituales en comentarios técnicos
const lowlight = createLowlight({
	bash:       common.bash,
	css:        common.css,
	html:       common.xml,
	javascript: common.javascript,
	json:       common.json,
	php:        common.php,
	python:     common.python,
	sql:        common.sql,
	typescript: common.typescript,
	yaml:       common.yaml,
});

const MAX_IMAGE_BYTES = 10 * 1024 * 1024;
let tiptapInstanceSequence = 0;

const FILE_SIGNATURES = [
	{ mime: 'image/png', bytes: [0x89, 0x50, 0x4e, 0x47] },
	{ mime: 'image/jpeg', bytes: [0xff, 0xd8, 0xff] },
	{ mime: 'image/gif', bytes: [0x47, 0x49, 0x46, 0x38] },
	{
		mime: 'image/webp',
		customCheck: (header) =>
			header[0] === 0x52
			&& header[1] === 0x49
			&& header[2] === 0x46
			&& header[3] === 0x46
			&& header[8] === 0x57
			&& header[9] === 0x45
			&& header[10] === 0x42
			&& header[11] === 0x50,
	},
];

function escapeHtml(value) {
	return String(value)
		.replaceAll('&', '&amp;')
		.replaceAll('<', '&lt;')
		.replaceAll('>', '&gt;')
		.replaceAll('"', '&quot;')
		.replaceAll("'", '&#39;');
}

async function detectImageMime(file) {
	const header = new Uint8Array(await file.slice(0, 16).arrayBuffer());

	for (const signature of FILE_SIGNATURES) {
		if (typeof signature.customCheck === 'function' && signature.customCheck(header)) {
			return signature.mime;
		}

		if (Array.isArray(signature.bytes)) {
			const matches = signature.bytes.every((byte, index) => header[index] === byte);

			if (matches) {
				return signature.mime;
			}
		}
	}

	return null;
}

function fileToDataUrl(file) {
	return new Promise((resolve, reject) => {
		const reader = new FileReader();

		reader.onload = () => resolve(String(reader.result || ''));
		reader.onerror = () => reject(new Error('No se pudo leer la imagen.'));

		reader.readAsDataURL(file);
	});
}

function formatBytes(bytes) {
	return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

window.tiptapEditor = function tiptapEditor(options = {}) {
	const {
		initialValue = '',
		maxCommentBytes = 10 * 1024 * 1024,
		messages = {},
	} = options;

	const i18n = {
		imageTooLarge: messages.imageTooLarge || 'La imagen supera el límite de 2 MB.',
		imageInvalidType: messages.imageInvalidType || 'Tipo de imagen no válido.',
		commentTooLarge: messages.commentTooLarge || 'El comentario supera el límite de 10 MB.',
		invalidLink: messages.invalidLink || 'El enlace debe comenzar por http:// o https://',
	};

	// Variables de clausura fuera del estado reactivo de Alpine.
	// Alpine 3 envuelve todo el objeto retornado en un Proxy reactivo.
	// ProseMirror falla con "Applying a mismatched transaction" cuando se opera
	// sobre el editor a través del Proxy, porque las comparaciones internas de
	// identidad de objetos (tr.before !== state.doc) se rompen.
	// rawEditor almacena la referencia REAL (sin Proxy) del Editor de Tiptap.
	let rawEditor = null;
	let slashCommandFn = null;

	return {
		instanceId: ++tiptapInstanceSequence,
		editor: null,
		html: initialValue || '',
		isEmpty: true,
		maxCommentBytes,
		initialized: false,

		// Estado del slash menu
		slash: {
			show: false,
			query: '',
			items: [],
			selectedIndex: 0,
		},

		getActiveEditor() {
			if (!rawEditor || rawEditor.isDestroyed) {
				return null;
			}

			return rawEditor;
		},

		init() {
			if (this.initialized) {
				return;
			}

			if (this.$el.__tiptapController && this.$el.__tiptapController !== this) {
				this.$el.__tiptapController.destroy();
			}

			if (this.$refs.editorEl.__tiptapEditor) {
				this.$refs.editorEl.__tiptapEditor.destroy();
				this.$refs.editorEl.__tiptapEditor = null;
			}

			this.initialized = true;
			this.$el.__tiptapController = this;

			const self = this;

			// Listener nativo en la toolbar — evita Alpine eval (CSP).
			// Un único listener en el contenedor gestiona todos los botones (delegación).
			if (this.$refs.toolbarEl) {
				this.$refs.toolbarEl.addEventListener('mousedown', (e) => {
					e.preventDefault(); // evita que el editor pierda el foco/selección
					const btn = e.target.closest('[data-toolbar-cmd]');
					if (btn) self.execCommand(btn.dataset.toolbarCmd);
				});
			}

			// Extensión de slash commands basada en Suggestion
			const SlashCommands = Extension.create({
				name: 'slashCommands',
				addOptions() {
					return { suggestion: {} };
				},
				addProseMirrorPlugins() {
					return [
						Suggestion({
							editor: this.editor,
							char: '/',
							allowSpaces: false,
							startOfLine: false,
							...this.options.suggestion,
						}),
					];
				},
			});

			rawEditor = new Editor({
			// NOTA: rawEditor es la referencia real. this.editor es el Proxy de Alpine
			// que se asigna después para que templates Alpine puedan acceder si lo necesitan.
				element: this.$refs.editorEl,
				extensions: [
					StarterKit.configure({
						link: {
							openOnClick: 'whenNotEditable',
							autolink: true,
							protocols: ['http', 'https'],
							HTMLAttributes: {
								target: '_blank',
								rel: 'noopener noreferrer nofollow',
							},
						},
						codeBlock: false,  // lo reemplaza CodeBlockLowlight
						underline: false,  // lo registra Underline explícitamente
					}),
					Image.configure({ allowBase64: true }),
					TaskList,
					TaskItem.configure({ nested: true }),
					CodeBlockLowlight.configure({ lowlight }),
					TextAlign.configure({ types: ['heading', 'paragraph'] }),
					Underline,
					Highlight,
					TextStyle,
					Color,
					Superscript,
					Subscript,
					Placeholder.configure({
						placeholder: self.$refs.editorEl.dataset.placeholder || '',
					}),
					SlashCommands.configure({
						suggestion: {
							items: ({ query }) => self.slashItems(query),
							// command: callback que llama Tiptap cuando se ejecuta props.command(item).
							// Recibe editor y range NATIVOS de ProseMirror (sin Proxy de Alpine).
							command: ({ editor, range, props: item }) => {
								if (item.command === 'image') {
									editor.chain().focus().deleteRange(range).run();
									setTimeout(() => self.triggerImagePicker(), 0);
									return;
								}
								let chain = editor.chain().focus().deleteRange(range);
								switch (item.command) {
									case 'paragraph':   chain = chain.setParagraph(); break;
									case 'h1':          chain = chain.setHeading({ level: 1 }); break;
									case 'h2':          chain = chain.setHeading({ level: 2 }); break;
									case 'h3':          chain = chain.setHeading({ level: 3 }); break;
									case 'bulletList':  chain = chain.toggleBulletList(); break;
									case 'orderedList': chain = chain.toggleOrderedList(); break;
									case 'taskList':    chain = chain.toggleTaskList(); break;
									case 'blockquote':  chain = chain.toggleBlockquote(); break;
									case 'hr':          chain = chain.setHorizontalRule(); break;
									case 'codeBlock':   chain = chain.toggleCodeBlock(); break;
								}
								chain.run();
							},
							render: () => ({
								onStart(props) {
									slashCommandFn = props.command;
									self.slash.show = true;
									self.slash.query = props.query;
									self.slash.items = self.slashItems(props.query);
									self.slash.selectedIndex = 0;
									self.positionSlashMenu(props.clientRect);
								},
								onUpdate(props) {
									slashCommandFn = props.command;
									self.slash.query = props.query;
									self.slash.items = self.slashItems(props.query);
									self.slash.selectedIndex = 0;
									self.positionSlashMenu(props.clientRect);
								},
								onKeyDown({ event }) {
									if (!self.slash.show) return false;
									if (event.key === 'ArrowDown') {
										self.slash.selectedIndex = (self.slash.selectedIndex + 1) % self.slash.items.length;
										return true;
									}
									if (event.key === 'ArrowUp') {
										self.slash.selectedIndex = (self.slash.selectedIndex - 1 + self.slash.items.length) % self.slash.items.length;
										return true;
									}
									if (event.key === 'Enter') {
										const item = self.slash.items[self.slash.selectedIndex];
										if (item && slashCommandFn) {
											slashCommandFn(item);
										}
										return true;
									}
									if (event.key === 'Escape') {
										self.slash.show = false;
										return true;
									}
									return false;
								},
								onExit() {
									slashCommandFn = null;
									self.slash.show = false;
								},
							}),
						},
					}),
				],
				content: this.html,
				editorProps: {
					attributes: { class: 'rte-prosemirror rte-content' },
				},
				onCreate({ editor }) {
					self.editor = rawEditor; // exponer al Proxy de Alpine
					self.html = editor.getHTML();
					self.isEmpty = editor.isEmpty;
					self.$refs.editorEl.__tiptapEditor = editor;
				},
				onUpdate({ editor }) {
					self.html = editor.getHTML();
					self.isEmpty = editor.isEmpty;
				},
				// onTransaction se dispara tras CADA transacción (incluye selección,
				// formato, inserción, etc.) — actualiza is-active en la toolbar
				// inmediatamente, no solo al cambiar la selección.
				onTransaction({ editor }) {
					self.updateToolbarState(editor);
				},
			});
		},

		// --- Toolbar state ---

		updateToolbarState(editor) {
			const toolbarEl = this.$refs.toolbarEl;
			if (!toolbarEl) return;
			const cmdActive = {
				bold:        editor.isActive('bold'),
				italic:      editor.isActive('italic'),
				underline:   editor.isActive('underline'),
				strike:      editor.isActive('strike'),
				link:        editor.isActive('link'),
				code:        editor.isActive('code'),
				highlight:   editor.isActive('highlight'),
				alignLeft:   editor.isActive({ textAlign: 'left' }),
				alignCenter: editor.isActive({ textAlign: 'center' }),
				alignRight:  editor.isActive({ textAlign: 'right' }),
				superscript: editor.isActive('superscript'),
				subscript:   editor.isActive('subscript'),
			};
			toolbarEl.querySelectorAll('[data-toolbar-cmd]').forEach(btn => {
				btn.classList.toggle('is-active', !!cmdActive[btn.dataset.toolbarCmd]);
			});
		},

		// --- Slash menu helpers ---

		slashItems(query) {
			const all = [
				{ label: 'Texto',           icon: '¶',  command: 'paragraph' },
				{ label: 'Título 1',        icon: 'H1', command: 'h1' },
				{ label: 'Título 2',        icon: 'H2', command: 'h2' },
				{ label: 'Título 3',        icon: 'H3', command: 'h3' },
				{ label: 'Lista de puntos', icon: '•',  command: 'bulletList' },
				{ label: 'Lista numerada',  icon: '1.', command: 'orderedList' },
				{ label: 'Lista de tareas', icon: '☐',  command: 'taskList' },
				{ label: 'Cita',            icon: '❝',  command: 'blockquote' },
				{ label: 'Separador',       icon: '—',  command: 'hr' },
				{ label: 'Código',          icon: '<>', command: 'codeBlock' },
				{ label: 'Imagen',          icon: '🖼', command: 'image' },
			];
			const q = (query || '').toLowerCase();
			return q ? all.filter(item => item.label.toLowerCase().includes(q)) : all;
		},

		executeSlashItem(item) {
			if (!item || !slashCommandFn) return;
			const fn = slashCommandFn;
			this.slash.show = false;
			fn(item); // Tiptap-Suggestion gestiona deleteRange + comando en una transacción atómica
		},

		positionSlashMenu(clientRect) {
			if (!clientRect || !this.$refs.slashMenuEl) return;
			const rect = typeof clientRect === 'function' ? clientRect() : clientRect;
			const el = this.$refs.slashMenuEl;
			const editorRect = this.$el.getBoundingClientRect();
			el.style.top  = `${rect.bottom - editorRect.top + 4}px`;
			el.style.left = `${rect.left - editorRect.left}px`;
		},

		validateCommentSize() {
			const bytes = new TextEncoder().encode(this.html).length;

			if (bytes <= this.maxCommentBytes) {
				return true;
			}

			this.notifyError(`${i18n.commentTooLarge} (${formatBytes(bytes)})`);
			return false;
		},

		async insertImageFile(file) {
			if (!file) {
				return;
			}

			if (file.size > MAX_IMAGE_BYTES) {
				this.notifyError(`${i18n.imageTooLarge} (${formatBytes(file.size)})`);
				return;
			}

			const detectedMime = await detectImageMime(file);
			if (!detectedMime) {
				this.notifyError(i18n.imageInvalidType);
				return;
			}

			const dataUrl = await fileToDataUrl(file);
			if (!dataUrl.startsWith(`data:${detectedMime};base64,`)) {
				this.notifyError(i18n.imageInvalidType);
				return;
			}

			const editor = this.getActiveEditor();
			if (!editor) {
				return;
			}

			// Tiptap escapa alt/title internamente — no usar escapeHtml aquí
			// (causaría doble codificación: &amp; en vez de &).
			editor
				.chain()
				.focus()
				.setImage({
					src: dataUrl,
					alt: file.name || 'image',
					title: file.name || 'image',
				})
				.run();

			this.html = editor.getHTML() || this.html;
			this.validateCommentSize();
		},

		async handleFiles(fileList) {
			const files = Array.from(fileList || []);
			const imageFiles = files.filter((file) => file instanceof File);

			for (const file of imageFiles) {
				await this.insertImageFile(file);
			}
		},

		async handlePaste(event) {
			const items = Array.from(event.clipboardData?.items || []);
			const imageItem = items.find((item) => item.kind === 'file');

			if (!imageItem) {
				return;
			}

			event.preventDefault();
			await this.insertImageFile(imageItem.getAsFile());
		},

		async handleDrop(event) {
			const files = event.dataTransfer?.files;
			if (!files || files.length === 0) {
				return;
			}

			event.preventDefault();
			await this.handleFiles(files);
		},

		promptForLink() {
			const editor = this.getActiveEditor();
			if (!editor) {
				return;
			}

			const currentHref = editor.getAttributes('link')?.href || '';
			const value = window.prompt('URL', currentHref);

			if (value === null) {
				return;
			}

			const normalized = value.trim();

			if (normalized === '') {
				editor.chain().unsetLink().run();
				return;
			}

			if (!/^https?:\/\//i.test(normalized)) {
				this.notifyError(i18n.invalidLink);
				return;
			}

			editor.chain().extendMarkRange('link').setLink({ href: normalized }).run();
		},

		execCommand(command) {
			const editor = this.getActiveEditor();
			if (!editor) {
				return;
			}

			try {
				// link abre un prompt — se gestiona aparte (necesita foco explícito)
				if (command === 'link') {
					this.promptForLink();
					return;
				}

				// Todos los demás comandos: sin .focus() — e.preventDefault() en mousedown
				// ya mantiene el foco y la selección ProseMirror intactos.
				// Llamar .focus() aquí resetea la selección antes de ejecutar el comando.
				let chain = editor.chain();
				switch (command) {
					case 'bold':         chain = chain.toggleBold(); break;
					case 'italic':       chain = chain.toggleItalic(); break;
					case 'underline':    chain = chain.toggleUnderline(); break;
					case 'strike':       chain = chain.toggleStrike(); break;
					case 'code':         chain = chain.toggleCode(); break;
					case 'highlight':    chain = chain.toggleHighlight(); break;
					case 'alignLeft':    chain = chain.setTextAlign('left'); break;
					case 'alignCenter':  chain = chain.setTextAlign('center'); break;
					case 'alignRight':   chain = chain.setTextAlign('right'); break;
					case 'superscript':  chain = chain.toggleSuperscript(); break;
					case 'subscript':    chain = chain.toggleSubscript(); break;
					case 'h2':           chain = chain.toggleHeading({ level: 2 }); break;
					case 'h3':           chain = chain.toggleHeading({ level: 3 }); break;
					case 'bulletList':   chain = chain.toggleBulletList(); break;
					case 'orderedList':  chain = chain.toggleOrderedList(); break;
					case 'taskList':     chain = chain.toggleTaskList(); break;
					case 'codeBlock':    chain = chain.toggleCodeBlock(); break;
					case 'image':        this.triggerImagePicker(); return;
					default: return;
				}
				chain.run();
			} catch (_error) {
				// Avoid crashing the UI if a command races with teardown.
			}
		},

		triggerImagePicker() {
			this.$refs.imageInput?.click();
		},

		async submitToWire(methodName, ...args) {
			if (!this.$wire) {
				this.notifyError('No se pudo enviar el comentario. Recarga la pagina e intenta de nuevo.');
				return;
			}

			const latestHtml = rawEditor?.getHTML() || this.html || '';
			this.html = latestHtml;

			if (!this.validateCommentSize()) {
				return;
			}

			if (typeof this.$wire.$call === 'function') {
				try {
					await this.$wire.$call(methodName, ...args, latestHtml);
				} catch (_error) {
					this.notifyError('La solicitud de guardado fallo. Revisa la consola y el log de Laravel.');
				}
				return;
			}

			this.notifyError('No se pudo ejecutar la accion de guardado.');
		},

		async onImageInputChange(event) {
			await this.handleFiles(event.target?.files || []);
			event.target.value = '';
		},

		notifyError(message) {
			this.$dispatch('notify', { type: 'error', message });
			window.alert(message);
		},

		destroy() {
			if (this.$refs?.editorEl && this.$refs.editorEl.__tiptapEditor === rawEditor) {
				this.$refs.editorEl.__tiptapEditor = null;
			}

			if (this.$el.__tiptapController === this) {
				this.$el.__tiptapController = null;
			}

			rawEditor?.destroy();
			rawEditor = null;
			this.editor = null;
			this.initialized = false;
		},
	};
};

