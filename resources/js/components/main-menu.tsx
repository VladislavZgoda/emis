import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { logout } from '@/routes';
import { Link } from '@inertiajs/react';

export default function MainMenu() {
    return (
        <NavigationMenu>
            <NavigationMenuList>
                <NavigationMenuItem>
                    <NavigationMenuLink
                        render={<Link href="#" />}
                        className={navigationMenuTriggerStyle()}
                    >
                        Просмотр данных
                    </NavigationMenuLink>
                </NavigationMenuItem>
                <NavigationMenuItem>
                    <NavigationMenuLink
                        render={<Link href="#" />}
                        className={navigationMenuTriggerStyle()}
                    >
                        Сформировать отчёты
                    </NavigationMenuLink>
                </NavigationMenuItem>
                <NavigationMenuItem>
                    <NavigationMenuLink
                        render={<Link href={logout()} as="button" />}
                        className={navigationMenuTriggerStyle()}
                    >
                        Выход
                    </NavigationMenuLink>
                </NavigationMenuItem>
            </NavigationMenuList>
        </NavigationMenu>
    );
}
