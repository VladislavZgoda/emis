import '@inertiajs/core';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            sidebarOpen: boolean;
            [key: string]: unknown;
            substations: { id: number; name: string }[];
        };
    }
}
