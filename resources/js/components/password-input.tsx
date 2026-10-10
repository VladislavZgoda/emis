import { Eye, EyeOff } from 'lucide-react';
import { ComponentProps, useState } from 'react';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';

export function PasswordInput(
    props: Omit<ComponentProps<typeof InputGroupInput>, 'type'>,
) {
    const [visible, setVisible] = useState(false);

    return (
        <InputGroup>
            <InputGroupInput {...props} type={visible ? 'text' : 'password'} />
            <InputGroupAddon align="inline-end">
                <InputGroupButton
                    type="button"
                    size="icon-xs"
                    aria-label={visible ? 'Скрыть пароль' : 'Показать пароль'}
                    aria-pressed={visible}
                    onClick={() => setVisible((v) => !v)}
                >
                    {visible ? <EyeOff /> : <Eye />}
                </InputGroupButton>
            </InputGroupAddon>
        </InputGroup>
    );
}
