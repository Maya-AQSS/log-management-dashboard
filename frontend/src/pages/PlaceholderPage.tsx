type PlaceholderPageProps = {
 title: string;
 description?: string;
};

export function PlaceholderPage({ title, description }: PlaceholderPageProps) {
 return (<div className="p-4">
 <h2 className="text-xl font-semibold text-on-surface">
 {title}
 </h2>
 <p className="mt-2 text-on-surface-muted">
 {description ??'Página pendiente de migración a React.'}
 </p>
 </div>
 );
}
