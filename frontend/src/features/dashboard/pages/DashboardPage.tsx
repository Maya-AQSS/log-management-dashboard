import { useCallback, useRef, useState } from'react';
import { useTranslation } from'react-i18next';
import { Button, PageTitle } from'@maya/shared-ui-react';
import {
 DashboardEditToggleButton,
 WidgetGrid,
 useDashboardLayoutLocal,
 type LayoutItem,
} from'@maya/shared-dashboard-react';
import { DEFAULT_LAYOUT, WIDGET_REGISTRY } from'../widgets/registry';

const STORAGE_KEY ='maya:logs:dashboard-layout';

function DashboardSkeleton() {
 return (<div className="p-4 sm:p-6 grid grid-cols-12 gap-4 animate-pulse">
 <div className="col-span-12 sm:col-span-4 h-32 bg-outline-variant rounded-2xl" />
 <div className="col-span-12 sm:col-span-8 h-32 bg-outline-variant rounded-2xl" />
 <div className="col-span-12 h-32 bg-outline-variant rounded-2xl" />
 </div>
 );
}

/**
 * Customizable dashboard for maya_logs. Layout persists to localStorage under
 *`maya:logs:dashboard-layout`. Toggle edit mode to drag, resize, or remove
 * widgets — changes are saved on exit.
 */
export function DashboardPage() {
 const { t } = useTranslation('dashboard');
 const { layout, loading, saveLayout, resetToDefault } = useDashboardLayoutLocal({
 storageKey: STORAGE_KEY,
 defaultLayout: DEFAULT_LAYOUT,
 });
 const [editable, setEditable] = useState(false);
 const [draftLayout, setDraftLayout] = useState<LayoutItem[] | null>(null);
 const snapshotRef = useRef<LayoutItem[] | null>(null);

 const activeLayout = editable ? (draftLayout ?? layout) : layout;

 const handleToggleEdit = useCallback(() => {
 setEditable((prev) => {
 if (prev) {
 setDraftLayout(null);
 return false;
 }
 snapshotRef.current = layout;
 setDraftLayout(layout);
 return true;
 });
 }, [layout]);

 const handleSave = useCallback(async () => {
 await saveLayout(draftLayout ?? layout);
 setEditable(false);
 setDraftLayout(null);
 }, [draftLayout, layout, saveLayout]);

 const handleCancel = useCallback(() => {
 setDraftLayout(null);
 setEditable(false);
 }, []);

 const handleLayoutChange = useCallback((next: LayoutItem[]) => {
 if (!editable) return;
 setDraftLayout(next);
 },
 [editable],
 );

 const handleRemoveWidget = useCallback((widgetId: string) => {
 setDraftLayout((prev) => (prev ?? layout).filter((item) => item.i !== widgetId));
 },
 [layout],
 );

 const handleReset = useCallback(async () => {
 await resetToDefault();
 setDraftLayout(null);
 setEditable(false);
 }, [resetToDefault]);

 if (loading) {
 return <DashboardSkeleton />;
 }

 return (<div className="px-4 py-2">
 <div className="flex items-center justify-between mb-3">
 <PageTitle title={t('title')} />
 <div className="flex items-center gap-2">
 {editable && (<>
 <Button variant="secondary" size="sm" onClick={handleReset}>
 {t('edit.reset')}
 </Button>
 <Button variant="secondary" size="sm" onClick={handleCancel}>
 {t('edit.cancel')}
 </Button>
 <Button variant="primary" size="sm" onClick={handleSave}>
 {t('edit.save')}
 </Button>
 </>
 )}
 {!editable && (<DashboardEditToggleButton
 editable={editable}
 onToggle={handleToggleEdit}
 editLabel={t('edit.toggle')}
 />
 )}
 </div>
 </div>

 <WidgetGrid
 registry={WIDGET_REGISTRY}
 layout={activeLayout}
 onLayoutChange={handleLayoutChange}
 editable={editable}
 onRemoveWidget={handleRemoveWidget}
 t={t}
 emptyKey="widgets.empty"
 removeAriaLabel={t('edit.removeWidget')}
 />
 </div>
 );
}

export default DashboardPage;
