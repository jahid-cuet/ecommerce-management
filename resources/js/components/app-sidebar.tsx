import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, Package, Store, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import owner from '@/routes/owner';
import type { NavItem, UserRole } from '@/types';

const navItemsByRole: Record<UserRole, NavItem[]> = {
    admin: [
        {
            title: 'Dashboard',
            href: admin.dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Shops',
            href: admin.shops.index(),
            icon: Store,
        },
        {
            title: 'Products',
            href: admin.products.index(),
            icon: Package,
        },
        {
            title: 'Shop owners',
            href: admin.shopOwners.index(),
            icon: Users,
        },
    ],
    shop_owner: [
        {
            title: 'Dashboard',
            href: owner.dashboard(),
            icon: LayoutGrid,
        },
    ],
};

export function AppSidebar() {
    const { auth } = usePage().props;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItemsByRole[auth.user.role]} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
