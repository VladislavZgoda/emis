import { store } from '@/actions/App/Http/Controllers/AuthenticatedSessionController';
import { PasswordInput } from '@/components/password-input';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Form, Head } from '@inertiajs/react';
import { Zap } from 'lucide-react';

export default function Login() {
    return (
        <>
            <Head title="Вход" />
            <div className="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div className="flex w-full max-w-sm flex-col gap-6">
                    <div className="flex flex-col items-center gap-3 self-center text-center font-medium">
                        <div className="bg-primary text-primary-foreground flex size-10 shrink-0 items-center justify-center rounded-lg">
                            <Zap className="size-6" />
                        </div>
                        <span className="text-balance">
                            АСКУЭ: учёт оборудования подстанций
                        </span>
                    </div>
                    <Form<{ username: string; password: string }>
                        {...store.form()}
                        disableWhileProcessing
                    >
                        {({ errors, processing }) => (
                            <Card className="w-full max-w-sm">
                                <CardHeader>
                                    <CardTitle>
                                        Войдите в свою учетную запись
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <FieldGroup>
                                        <Field data-invalid={!!errors.username}>
                                            <FieldLabel htmlFor="username">
                                                Имя пользователя
                                            </FieldLabel>
                                            <Input
                                                type="text"
                                                name="username"
                                                id="username"
                                                aria-invalid={!!errors.username}
                                                required
                                            />
                                            {errors.username && (
                                                <FieldError>
                                                    {errors.username}
                                                </FieldError>
                                            )}
                                        </Field>
                                        <Field data-invalid={!!errors.username}>
                                            <FieldLabel htmlFor="password">
                                                Пароль
                                            </FieldLabel>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                aria-invalid={!!errors.username}
                                                required
                                            />
                                        </Field>
                                    </FieldGroup>
                                </CardContent>
                                <CardFooter className="flex-col gap-2">
                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing}
                                    >
                                        {processing ? 'Вход...' : 'Войти'}
                                    </Button>
                                </CardFooter>
                            </Card>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
