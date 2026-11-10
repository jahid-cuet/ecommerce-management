import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Props = {
    /** The page URL to reload with ?search=... */
    url: string;
    initialValue: string;
    placeholder: string;
};

/**
 * A search box that reloads the current list page with the search term in the query string.
 */
export default function SearchForm({ url, initialValue, placeholder }: Props) {
    const [search, setSearch] = useState(initialValue);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            url,
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    };

    return (
        <form onSubmit={submit} className="flex max-w-md gap-2">
            <Input
                type="search"
                name="search"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder={placeholder}
                aria-label={placeholder}
            />
            <Button type="submit" variant="outline">
                <Search />
                Search
            </Button>
        </form>
    );
}
