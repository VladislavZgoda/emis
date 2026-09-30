import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarInput,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { HousePlug, Search, Zap } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { Label } from './ui/label';
import { useMemo, useState } from 'react';
import AppearanceToggleTab from './appearance-tabs';
import { CreateSubstationDialog } from './create-substation-dialog';

export default function AppSidebar() {
    const { substations } = usePage().props;
    const [query, setQuery] = useState('');

    const filteredSubstations = useMemo(
        () =>
            query
                ? substations.filter((s) =>
                      s.name.toLowerCase().includes(query.toLowerCase()),
                  )
                : substations,
        [substations, query],
    );

    return (
        <Sidebar variant="floating">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg">
                            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                                <Zap className="size-4" />
                            </div>
                            <div className="leading-none font-medium">
                                Информационная система управления
                                энергопотреблением
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <div className="flex gap-2">
                    <div className="relative flex-1">
                        <Label htmlFor="search" className="sr-only">
                            Поиск
                        </Label>
                        <SidebarInput
                            id="search"
                            placeholder="Поиск подстанции..."
                            className="pl-8"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                        />
                        <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 opacity-50 select-none" />
                    </div>
                    <CreateSubstationDialog />
                </div>
            </SidebarHeader>
            <SidebarContent>
                <SidebarGroup>
                    <SidebarMenu className="gap-2">
                        {filteredSubstations.length ? (
                            filteredSubstations.map((s) => (
                                <SidebarMenuItem key={s.id}>
                                    <SidebarMenuButton
                                        render={
                                            <Link className="font-medium" />
                                        }
                                    >
                                        <HousePlug />
                                        <span className="tabular-nums">
                                            {s.name}
                                        </span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))
                        ) : (
                            <p className="text-muted-foreground px-2 text-sm">
                                {query
                                    ? 'Ничего не найдено'
                                    : 'Подстанции ещё не добавлены'}
                            </p>
                        )}
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>
            <SidebarFooter>
                <AppearanceToggleTab />
            </SidebarFooter>
        </Sidebar>
    );
}
