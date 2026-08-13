<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <GuestLayout>
        <CardHeader>
            <CardTitle>Log in</CardTitle>
        </CardHeader>

        <CardContent>
            <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-500">
                {{ status }}
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-muted-foreground">Remember me</span>
                </label>

                <div class="flex items-center justify-end gap-4">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-muted-foreground underline hover:text-foreground"
                    >
                        Forgot your password?
                    </Link>

                    <Button type="submit" :disabled="form.processing">Log in</Button>
                </div>
            </form>
        </CardContent>
    </GuestLayout>
</template>
