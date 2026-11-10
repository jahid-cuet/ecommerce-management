import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, home, login } from '@/routes';

/**
 * The public layout for customers browsing shops and products.
 */
export default function StoreLayout({ children }: { children: ReactNode }) {
    const { auth, name } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="border-b">
                <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-4">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 font-semibold"
                    >
                        <span className="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <AppLogoIcon className="size-5 fill-current" />
                        </span>
                        {name}
                    </Link>
                    <Button asChild variant="outline" size="sm">
                        {auth.user ? (
                            <Link href={dashboard()}>Dashboard</Link>
                        ) : (
                            <Link href={login()}>Log in</Link>
                        )}
                    </Button>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                {children}
            </main>

            <footer className="border-t">
                <div className="mx-auto w-full max-w-6xl px-4 py-6 text-sm text-muted-foreground">
                    © {new Date().getFullYear()} {name}
                </div>
            </footer>
        </div>
    );
}
