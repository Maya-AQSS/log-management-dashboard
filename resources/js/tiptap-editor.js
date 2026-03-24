import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
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

	return {
		instanceId: ++tiptapInstanceSequence,
		editor: null,
		html: initialValue || '',
		isEmpty: true,
		maxCommentBytes,
		resetHandler: null,
		initialized: false,

		getActiveEditor() {
			const editor = this.editor;

			if (!editor || editor.isDestroyed) {
				return null;
			}

			return editor;
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

			this.editor = new Editor({
				element: this.$refs.editorEl,
				extensions: [
					StarterKit.configure({
						link: {
							openOnClick: false,
							autolink: true,
							protocols: ['http', 'https'],
						},
					}),
					Image,
				],
				content: this.html,
				editorProps: {
					attributes: {
						class: 'rte-prosemirror',
					},
				},
				onCreate({ editor }) {
					self.html = editor.getHTML();
					self.isEmpty = editor.isEmpty;
					self.$refs.editorEl.__tiptapEditor = editor;
				},
				onUpdate({ editor }) {
					self.html = editor.getHTML();
					self.isEmpty = editor.isEmpty;
				},
			});

			this.resetHandler = () => {
				if (!this.editor) {
					return;
				}

				try {
					this.editor.commands.clearContent(true);
					this.html = this.editor.getHTML();
					this.isEmpty = this.editor.isEmpty;
				} catch (_error) {
					// Editor may be in teardown while Livewire morphs the DOM.
				}
			};

			this.$el.addEventListener('comment-editor-reset', this.resetHandler);
			window.addEventListener('comment-editor-reset', this.resetHandler);
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

			this.editor
				?.chain()
				.focus()
				.setImage({
					src: dataUrl,
					alt: escapeHtml(file.name || 'image'),
					title: escapeHtml(file.name || 'image'),
				})
				.run();

			this.html = this.editor?.getHTML() || this.html;
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
			switch (command) {
				case 'bold':
					editor.commands.toggleBold();
					break;
				case 'italic':
					editor.commands.toggleItalic();
					break;
				case 'strike':
					editor.commands.toggleStrike();
					break;
				case 'h2':
					editor.commands.toggleHeading({ level: 2 });
					break;
				case 'bulletList':
					editor.commands.toggleBulletList();
					break;
				case 'orderedList':
					editor.commands.toggleOrderedList();
					break;
				case 'link':
					this.promptForLink();
					break;
				default:
					break;
			}
			} catch (_error) {
				// Avoid crashing the UI if a command races with teardown.
			}
		},

		triggerImagePicker() {
			this.$refs.imageInput?.click();
		},

		async submitToWire(methodName) {
			if (!this.$wire) {
				this.notifyError('No se pudo enviar el comentario. Recarga la pagina e intenta de nuevo.');
				return;
			}

			const latestHtml = this.editor?.getHTML() || this.html || '';
			this.html = latestHtml;

			if (!this.validateCommentSize()) {
				return;
			}

			if (typeof this.$wire.$call === 'function') {
				try {
					await this.$wire.$call(methodName, latestHtml);
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
			if (this.resetHandler) {
				this.$el.removeEventListener('comment-editor-reset', this.resetHandler);
				window.removeEventListener('comment-editor-reset', this.resetHandler);
			}

			if (this.$refs?.editorEl && this.$refs.editorEl.__tiptapEditor === this.editor) {
				this.$refs.editorEl.__tiptapEditor = null;
			}

			if (this.$el.__tiptapController === this) {
				this.$el.__tiptapController = null;
			}

			this.editor?.destroy();
			this.editor = null;
			this.initialized = false;
		},
	};
};

