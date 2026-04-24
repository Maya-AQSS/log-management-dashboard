type PlaceholderPageProps = {
  title: string;
  description?: string;
};

export function PlaceholderPage({ title, description }: PlaceholderPageProps) {
  return (
    <div className="p-4">
      <h2 className="text-xl font-semibold text-text-primary dark:text-text-dark-primary">
        {title}
      </h2>
      <p className="mt-2 text-text-muted dark:text-text-dark-muted">
        {description ?? 'Página pendiente de migración a React.'}
      </p>
    </div>
  );
}
