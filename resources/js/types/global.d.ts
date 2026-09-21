declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
