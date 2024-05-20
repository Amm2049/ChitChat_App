<template>
    <div class="flex justify-center mt-40">
        <form @submit.prevent="false" class="p-5 w-96 rounded-sm inline-block bg-slate-900 text m-auto text-blue-200">
            <div class="m-5 text-3xl font-bold w-full">Register Account</div>
            <div class="m-0 ml-5 p-0 text-red-500">{{ error }}</div>
            <div class="m-5 flex flex-col">
                <input ref="name" class="my-3 pl-5 bg-slate-800 rounded-sm py-3 px-2" type="text"
                    placeholder="Enter user name ...">
                <input ref="email" class="my-3 pl-5 bg-slate-800 rounded-sm py-3 px-2" type="text"
                    placeholder="Enter user email ...">
                <input ref="password" class="my-3 pl-5 bg-slate-800 rounded-sm py-3 px-2" type="text"
                    placeholder="Enter user password ...">
            </div>
            <div class="m-5 text-center">
                <button v-if="!loading" :disabled="loading" :onclick="handleRegister"
                    class="bg-blue-500 text-black hover:bg-cyan-300 rounded-sm py-2 w-full">Register</button>
                <button v-else disabled class=" bg-cyan-900 rounded-sm py-2 w-full">
                    Registering ...
                </button>
            </div>
            <div class="ml-5 pb-5">
                Already have account ? <router-link to="/login" class=" text-blue-600">Login Page</router-link>
            </div>
        </form>
    </div>
</template>

<script>
import { ref } from 'vue';
import { config } from '@/config';
import { useRouter } from 'vue-router';

export default {
    setup() {

        const name = ref('')
        const email = ref('')
        const password = ref('')
        const error = ref(null)
        const loading = ref(false)

        const router = useRouter()

        const handleRegister = async () => {

            const newUser = {
                name: name.value.value, email: email.value.value, password: password.value.value
            }

            if (newUser.name !== '', newUser.email !== '', newUser.password !== '') {
                loading.value = true
                const response = await fetch(`${config.apiUrl}register`, {
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    body: JSON.stringify(newUser)
                })
                const dataFromServer = await response.json()
                loading.value = false
                if (dataFromServer.success) {
                    router.push({ name: 'login' })
                } else {
                    error.value = dataFromServer.error
                }
            }

        }

        return { name, email, password, error, loading, handleRegister }
    }
}

</script>

<style>
body {
    background-color: rgb(0, 0, 0);
}
</style>