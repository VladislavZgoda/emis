import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Monitor, Moon, Sun, type LucideIcon } from 'lucide-react';
import { HTMLAttributes } from 'react';
import { useAppearance, type Appearance } from '@/hooks/use-appearance';

const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
    { value: 'light', icon: Sun, label: 'Светлая' },
    { value: 'dark', icon: Moon, label: 'Тёмная' },
    { value: 'system', icon: Monitor, label: 'Системная' },
];

export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();
    const current = tabs.find((t) => t.value === appearance) ?? tabs[2];
    const CurrentIcon = current.icon;

    return (
        <div className={className} {...props}>
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger render={<SidebarMenuButton />}>
                            <CurrentIcon />
                            <span>{current.label}</span>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            side="top"
                            align="start"
                            className="w-(--radix-dropdown-menu-trigger-width) min-w-56"
                        >
                            {tabs.map(({ value, icon: Icon, label }) => (
                                <DropdownMenuItem
                                    key={value}
                                    onClick={() => updateAppearance(value)}
                                >
                                    <Icon className="size-4" />
                                    <span>{label}</span>
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </div>
    );
}
