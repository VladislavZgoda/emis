import { PropsWithChildren, useEffect } from 'react';
import { Toaster, toast } from '@/components/ui/toast';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import AppSidebar from '@/components/app-siderbar';
import { usePage, router } from '@inertiajs/react';
import MainMenu from '@/components/main-menu';

export default function Layout({ children }: PropsWithChildren) {
    const isOpen = usePage().props.sidebarOpen;

    useEffect(() => {
        return router.on('flash', (event) => {
            if (event.detail.flash.message) {
                toast.add({
                    type: 'success',
                    description: event.detail.flash.message,
                });
            }
        });
    }, []);

    return (
        <SidebarProvider defaultOpen={isOpen}>
            <AppSidebar />
            <main className="min-w-0 flex-1">
                <header className="flex items-center gap-2 border-b p-2">
                    <SidebarTrigger />
                    <MainMenu />
                </header>
                <article>{children}</article>
                <Toaster />
            </main>
        </SidebarProvider>
    );
}
