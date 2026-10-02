import { PropsWithChildren, useEffect } from 'react';
import { Toaster, toast } from '@/components/ui/toast';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import AppSidebar from '@/components/app-siderbar';
import { usePage, router } from '@inertiajs/react';

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
            <main>
                <SidebarTrigger />
                <article>{children}</article>
                <Toaster />
            </main>
        </SidebarProvider>
    );
}
