<template>
    <div class="flex justify-center mt-40">
        <form @submit.prevent="false" class="p-5 w-96  rounded-sm inline-block bg-slate-900 text m-auto text-blue-200">
            <div class="m-5 text-3xl font-bold w-full">Login Account</div>
            <div class="m-0 ml-5 p-0 text-red-500">{{ error }}</div>
            <div class="m-5 flex flex-col">
                <input ref="email" required class="my-3 pl-5 bg-slate-800 rounded-sm py-3 px-2" type="email"
                    placeholder="Enter your email ...">
                <input ref="password" required class="my-3 pl-5 bg-slate-800 rounded-sm py-3 px-2" type="text"
                    placeholder="Enter your password ...">
            </div>
            <div class="m-5 text-center">
                <button v-if="!loading" :disabled="loading" :onclick="login"
                    class="bg-blue-500 text-black hover:bg-cyan-300 rounded-sm py-2 w-full">Login</button>
                <button v-else disabled class=" bg-cyan-900 rounded-sm py-2 w-full">
                    Logging in ...
                </button>
            </div>
            <div class="ml-5 pb-5">
                Already have account ? <router-link to="/register" class=" text-blue-600">Register Page</router-link>
            </div>
        </form>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { config } from '@/config';

export default {
    setup() {
        const email = ref(null)
        const password = ref(null)
        const error = ref(null)
        const loading = ref(false)
        const store = useStore()
        const router = useRouter()

        const login = async () => {
            loading.value = true

            const user = {
                email: email.value.value,
                password: password.value.value
            }

            if (user.email && user.password) {
                const res = await fetch(`${config.apiUrl}login`, {
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    body: JSON.stringify(user)
                })
                if (res.ok) {
                    const userData = await res.json()
                    await localStorage.setItem('token', userData.token)
                    const token = localStorage.getItem("token");
                    const response = await fetch(`${config.apiUrl}userData`, {
                        method: "GET",
                        headers: {
                            Authorization: `Bearer ${token}`,
                            "Content-Type": "application/json", // Specify the content type if needed
                        },
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const { user, chatrooms, messages, allFriends, paginationForChatrooms } = data

                        await store.dispatch("setPaginationForChatrooms", paginationForChatrooms)

                        // Chatroom Images url
                        messages.map(item => {
                            item.data.map(message => {
                                if (message.content === '') {
                                    message.image = `${config.chatroomImages}${message.image}`
                                    message.imageLoaded = false
                                }
                            })
                        })
                        await store.dispatch("setMessagesData", messages)

                        // Friends
                        await allFriends.map(item => {
                            if (item.profileUrl !== null) {
                                const friendProfileUrl = `${config.profileImageUrl}${item.profileUrl}`
                                item.profileUrl = friendProfileUrl
                            } else {
                                const friendProfileUrl = `${config.defaultProfileImageUrl}`
                                item.profileUrl = friendProfileUrl
                            }

                        })
                        await store.dispatch("setAllFriendsData", allFriends)

                        // Chatroom partner image urls
                        await chatrooms.map(chatroom => {
                            if (chatroom.partner.profileUrl !== null) {
                                const friendProfileUrl = `${config.profileImageUrl}${chatroom.partner.profileUrl}`
                                chatroom.partner.profileUrl = friendProfileUrl
                            } else {
                                const friendProfileUrl = `${config.defaultProfileImageUrl}`
                                chatroom.partner.profileUrl = friendProfileUrl
                            }
                            chatroom.enterFirstTime = true
                        })
                        await store.dispatch("setChatroomsData", chatrooms)

                        // User image url
                        if (user.profileUrl !== null) {
                            await store.dispatch("setUserData", { ...user, profileUrl: config.profileImageUrl + user.profileUrl })
                        } else {
                            await store.dispatch("setUserData", { ...user, profileUrl: config.defaultProfileImageUrl })
                        }
                    }
                    router.push({ name: 'home' })
                    loading.value = false
                } else {
                    const errorMessage = await res.json()
                    error.value = errorMessage.error
                    loading.value = false
                }

            }
            loading.value = false

            const token = localStorage.getItem('token');
            // online
            await fetch(`${config.apiUrl}setUserOnline`, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
        }

        return { email, password, error, loading, login }
    }
}
</script>

<style>
body {
    background-color: rgb(0, 0, 0);
}
</style>