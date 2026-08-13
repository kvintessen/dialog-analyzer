<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <GuestLayout>
        <CardHeader>
            <CardTitle>Forgot Password</CardTitle>
            <CardDescription>
                Forgot your password? No problem. Just let us know your email address and we will email you a
                password reset link that will allow you to choose a new one.
            </CardDescription>
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

                <div class="flex items-center justify-end">
                    <Button type="submit" :disabled="form.processing">Email Password Reset Link</Button>
                </div>
            </form>
        </CardContent>
    </GuestLayout>
</template>
