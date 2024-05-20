import { useRouter } from 'vue-router';
<template>
    <!-- Side Bar  -->
    <div class="hidden sm:block md:block lg:block text-white bg-black w-1/12 md:w-1/12 lg:w-1/12">
        <div class="flex flex-col items-center w-full h-full">
            <div :class="{ 'bg-blue-500': isLinkActive('/') }" class="w-full text-center py-4 bg-black">
                <router-link to="/">
                    <div class="w-full">
                        <i class="fa-solid fa-house fa-lg"></i>
                    </div>
                </router-link>
            </div>
            <div :class="{ 'bg-blue-500': isLinkActive('/friends') }" class="w-full text-center py-4 bg-black">
                <router-link to="/friends">
                    <div class="w-full">
                        <i class="fa-solid fa-users fa-lg "></i>
                    </div>
                </router-link>
            </div>
            <div :class="{ 'bg-blue-500': isLinkActive('/profile') }" class="w-full text-center py-4 bg-black">
                <router-link to="/profile">
                    <div class="w-full">
                        <i class="fa-solid fa-circle-user fa-lg"></i>
                    </div>
                </router-link>
            </div>
            <button v-if="!loading" @click="logout"
                class="w-full text-center py-4 hover:bg-red-500 transition ease-out bg-black">Logout</button>
            <button v-else class="w-full text-center py-4 ease-out bg-red-500">
                Logging out
            </button>

        </div>
    </div>
</template>

<script>
import { useRouter } from "vue-router"
import { useStore } from 'vuex';
import { config } from '../config/index'
import { onMounted, ref } from 'vue';

export default {
    setup() {

        const router = useRouter()
        const store = useStore()
        const token = localStorage.getItem('token')
        const loading = ref(false)

        const logout = async () => {
            loading.value = true
            await fetch(`${config.apiUrl}setUserOffline`, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            loading.value = false
            router.push("/login")
            localStorage.clear()
        }

        const isLinkActive = (link) => {
            return router.currentRoute.value.fullPath == link;
        }

        // ------------- Side Bar End -------------

        // Retrieving Datas
        const getUserData = async () => {
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
        };

        onMounted(async () => {
            if (Object.keys(store.state.userData).length === 0 || Object.keys(store.state.chatroomsData).length === 0) {
                getUserData();

                // online
                await fetch(`${config.apiUrl}setUserOnline`, {
                    method: "GET",
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                })

                // Broadcasting
                var pusher = window.pusher;
                var channel = pusher.subscribe(`chatroom`);
                channel.bind('online', async function (data) {
                    const { userId } = data
                    if (userId !== store.getters.getUserData.id) {
                        await store.dispatch('setUserOnline', userId)
                    }
                })
                channel.bind('offline', async function (data) {
                    const { userId } = data
                    if (userId !== store.getters.getUserData.id) {
                        await store.dispatch('setUserOffline', userId)
                    }
                })
            }

            document.addEventListener('visibilitychange', async function () {
                if (document.visibilityState === 'visible') {
                    await fetch(`${config.apiUrl}setUserOnline`, {
                        method: "GET",
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    })
                } else {
                    await fetch(`${config.apiUrl}setUserOffline`, {
                        method: "GET",
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    })
                }
            });

        });

        onMounted(() => {

        })

        return { logout, isLinkActive, loading }
    }
}
</script>