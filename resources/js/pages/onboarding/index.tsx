import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { feed } from '@/routes';
import { store } from '@/routes/onboarding';
import { useState } from 'react';

type Country = { code: string; name: string };
type Language = { code: string; name: string };

type Props = {
    user: {
        id: number;
        name: string;
        email: string;
        birth_date: string | null;
    };
    countries: Country[];
    languages: Language[];
};

export default function Onboarding({ user, countries, languages }: Props) {
    const [step, setStep] = useState(1);
    const [birthDate, setBirthDate] = useState(user.birth_date ?? '');

    const canProceedToStep2 = birthDate !== '';

    return (
        <>
            <Head title="Set up your profile" />

            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={feed()}
                                className="flex flex-col items-center gap-2 font-medium"
                            >
                                <div className="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                                    <AppLogoIcon className="size-9 fill-current text-[var(--foreground)] dark:text-white" />
                                </div>
                                <span className="sr-only">Lenflo</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="text-xl font-medium">
                                    {step === 1
                                        ? 'When is your birthday?'
                                        : 'Where are you from?'}
                                </h1>
                                <p className="text-center text-sm text-muted-foreground">
                                    {step === 1
                                        ? 'We use this to show you age-appropriate content. Your birthday is not shown to others.'
                                        : 'Help us personalize your learning feed. You can skip this step.'}
                                </p>
                            </div>
                        </div>

                        <Form
                            {...store.form()}
                            resetOnSuccess={[]}
                            disableWhileProcessing
                            className="flex flex-col gap-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <input
                                        type="hidden"
                                        name="birth_date"
                                        value={birthDate}
                                    />

                                    {step === 1 && (
                                        <div className="grid gap-6">
                                            <div className="grid gap-2">
                                                <Label htmlFor="birth_date">
                                                    Date of birth
                                                </Label>
                                                <Input
                                                    id="birth_date"
                                                    type="date"
                                                    required
                                                    autoFocus
                                                    max={
                                                        new Date(
                                                            new Date().setFullYear(
                                                                new Date().getFullYear() -
                                                                    3,
                                                            ),
                                                        )
                                                            .toISOString()
                                                            .split('T')[0]
                                                    }
                                                    min="1900-01-01"
                                                    value={birthDate}
                                                    onChange={(e) =>
                                                        setBirthDate(
                                                            e.target.value,
                                                        )
                                                    }
                                                    tabIndex={1}
                                                />
                                            </div>

                                            <Button
                                                type="button"
                                                className="mt-2 w-full"
                                                tabIndex={2}
                                                disabled={!canProceedToStep2}
                                                onClick={() => setStep(2)}
                                            >
                                                Continue
                                            </Button>
                                        </div>
                                    )}

                                    {step === 2 && (
                                        <div className="grid gap-6">
                                            <div className="grid gap-2">
                                                <Label htmlFor="country_code">
                                                    Country
                                                </Label>
                                                <Select
                                                    name="country_code"
                                                    defaultValue=""
                                                >
                                                    <SelectTrigger tabIndex={1}>
                                                        <SelectValue placeholder="Select your country" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {countries.map(
                                                            (country) => (
                                                                <SelectItem
                                                                    key={
                                                                        country.code
                                                                    }
                                                                    value={
                                                                        country.code
                                                                    }
                                                                >
                                                                    {
                                                                        country.name
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.country_code
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="language_code">
                                                    Preferred language
                                                </Label>
                                                <Select
                                                    name="language_code"
                                                    defaultValue=""
                                                >
                                                    <SelectTrigger tabIndex={2}>
                                                        <SelectValue placeholder="Select a language" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {languages.map(
                                                            (language) => (
                                                                <SelectItem
                                                                    key={
                                                                        language.code
                                                                    }
                                                                    value={
                                                                        language.code
                                                                    }
                                                                >
                                                                    {
                                                                        language.name
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.language_code
                                                    }
                                                />
                                            </div>

                                            <div className="flex gap-3">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="flex-1"
                                                    tabIndex={3}
                                                    onClick={() => setStep(1)}
                                                >
                                                    Back
                                                </Button>
                                                <Button
                                                    type="submit"
                                                    className="flex-1"
                                                    tabIndex={4}
                                                    disabled={processing}
                                                    data-test="onboarding-submit"
                                                >
                                                    {processing && <Spinner />}
                                                    Get started
                                                </Button>
                                            </div>

                                            <Button
                                                type="submit"
                                                variant="ghost"
                                                className="w-full text-muted-foreground"
                                                tabIndex={5}
                                                disabled={processing}
                                                data-test="onboarding-skip"
                                            >
                                                Skip for now
                                            </Button>
                                        </div>
                                    )}
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            </div>
        </>
    );
}
