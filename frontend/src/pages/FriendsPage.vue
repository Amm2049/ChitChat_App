<template>
    <div class="h-screen flex justify-center">
        <!-- Side Bar  -->
        <SideBar />
        <div class="w-11/12 sm:px-3 h-full text-white overflow-y-scroll bg-slate-950">
            <div class="py-3 px-4 h-1/6 mb-5">
                <h1 class="text-4xl font-bold ml-2 mt-4">Friends List </h1>
                <div class="flex mt-6 ml-2 justify-between items-center">
                    <h5 @click="filterFriends('active')"
                        class="px-3 bg-slate-800 hover:bg-blue-950 py-2 rounded-full hover:cursor-pointer"
                        style="color: #00ff36;">
                        Online</h5>
                    <h5 @click="filterFriends('offline')"
                        class="px-3 py-2 bg-slate-800 hover:bg-blue-950 rounded-full hover:cursor-pointer"
                        style="color: #00b8ff;">Offline</h5>
                    <h5 @click="filterFriends('all')"
                        class="px-3 py-2 bg-slate-800 hover:bg-blue-950 rounded-full hover:cursor-pointer"
                        style="color:#d2ff00 ;">All Friends</h5>
                    <input style="border: 1px solid blue;" class=" bg-black px-5 py-2 w-1/4 rounded-3xl" type="text"
                        placeholder="Search your friends ...">
                </div>
            </div>
            <div class=" h-5/6">
                <div class="flex flex-wrap">
                    <div class="mt-8 ml-8 group relative w-1/8 h-40" v-for="friend in filteredFriends" :key="friend.id">
                        <img class=" w-40 rounded-2xl group-hover:opacity-50 h-full" :src="friend.profileUrl">
                        <div
                            class=" rounded-b-2xl text-white font-bold bg-gradient-to-t from-lime-500 bottom-0 absolute py-2 px-4 w-full">
                            <h1 class="">{{ friend.name }}</h1>
                        </div>
                        <div @click="sendMessage(friend.id)"
                            class="absolute opacity-0 group-hover:opacity-100 hover:bg-green-500 top-16 left-5 bg-slate-900 cursor-pointer px-2 py-1 rounded-lg">
                            {{ loading && friend.id === selected ? 'Creating room' : 'Send Message' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SideBar from '@/components/SideBar.vue'
import { ref, watchEffect } from 'vue';
import { config } from '@/config';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';

export default {
    components: { SideBar },
    setup() {

        const store = useStore()
        const loading = ref(false)
        const selected = ref(null)
        const router = useRouter()
        const token = localStorage.getItem("token")

        const filteredFriends = ref([])
        const friends = store.getters.getAllFriendsData
        filteredFriends.value = friends

        watchEffect(() => {
            const friends = store.getters.getAllFriendsData
            filteredFriends.value = friends
        })

        const filterFriends = (status) => {
            switch (status) {
                case 'active':
                    filteredFriends.value = friends.filter(friend => friend.online === 1)
                    break;
                case 'offline':
                    filteredFriends.value = friends.filter(friend => friend.online === 0)
                    break;
                case 'all':
                    filteredFriends.value = friends
                    break;

                default:
                    filteredFriends.value = friends
                    break;
            }
        }

        const sendMessage = async (id) => {
            loading.value = true
            selected.value = id

            const exist = store.getters.getChatroomsData.find(item => item.partner.id === id)
            if (!exist) {
                const response = await fetch(`${config.apiUrl}chatroom/create`,
                    {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ partnerId: id })
                    }
                )
                const dataFromServer = await response.json()
                const { chatrooms, messages, newChatroom, paginationForChatrooms } = dataFromServer
                await chatrooms.map(chatroom => {
                    const friendProfileUrl = `${config.profileImageUrl}${chatroom.partner.profileUrl}`
                    chatroom.partner.profileUrl = friendProfileUrl
                })
                await store.dispatch('setChatroomsData', chatrooms)
                await store.dispatch('setMessagesData', messages)
                await store.dispatch('setPaginationForChatrooms', paginationForChatrooms)

                const selectChatroomData = chatrooms.find(item => item.chatroom_id === newChatroom.id)
                await store.dispatch("setSelectedChatroomData", selectChatroomData)
                router.push({ name: 'home' })
            } else {
                const selectChatroomData = store.getters.getChatroomsData.find(item => item.id === exist.id)
                await store.dispatch("setSelectedChatroomData", selectChatroomData)
                router.push({ name: 'home' })
            }

            loading.value = false
            selected.value = null
        }

        return { store: store, sendMessage, loading, selected, filterFriends, filteredFriends }
    }
}

</script>