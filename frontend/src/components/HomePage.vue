<template>
    <div class="h-screen flex justify-center">
        <!-- Side Bar  -->
        <SideBar />

        <!-- Chats List -->
        <ChatList />

        <!-- Chat Room -->
        <ChatRoom />
    </div>
</template>

<script>
import ChatList from '@/components/ChatList.vue'
import ChatRoom from '@/components/ChatRoom.vue'
import SideBar from '@/components/SideBar.vue'

export default {
    components: { SideBar, ChatList, ChatRoom },
}
</script>

<style>
::-webkit-scrollbar {
    display: none;
}
</style>