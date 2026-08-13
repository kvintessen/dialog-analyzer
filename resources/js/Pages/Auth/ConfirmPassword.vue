<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirm Password" />

    <GuestLayout>
        <CardHeader>
            <CardTitle>Confirm Password</CardTitle>
            <CardDescription>
                This is a secure area of the application. Please confirm your password before continuing.
            </CardDescription>
        </CardHeader>

        <CardContent>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">Confirm</Button>
                </div>
            </form>
        </CardContent>
    </GuestLayout>
</template>
