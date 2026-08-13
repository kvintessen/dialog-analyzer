<script setup>
import { Button } from '@/components/ui/button';
import { CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <GuestLayout>
        <CardHeader>
            <CardTitle>Email Verification</CardTitle>
            <CardDescription>
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the
                link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </CardDescription>
        </CardHeader>

        <CardContent>
            <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600 dark:text-green-500">
                A new verification link has been sent to the email address you provided during registration.
            </div>

            <form class="flex items-center justify-between" @submit.prevent="submit">
                <Button type="submit" :disabled="form.processing">Resend Verification Email</Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-muted-foreground underline hover:text-foreground"
                >
                    Log Out
                </Link>
            </form>
        </CardContent>
    </GuestLayout>
</template>
