import { PropsWithChildren } from 'react';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import AppSidebar from '@/components/app-siderbar';
import { usePage } from '@inertiajs/react';

export default function Layout({ children }: PropsWithChildren) {
    const isOpen = usePage().props.sidebarOpen;

    return (
        <SidebarProvider defaultOpen={isOpen}>
            <AppSidebar />
            <main>
                <SidebarTrigger />
                <article>{children}</article>
            </main>
        </SidebarProvider>
    );
}
