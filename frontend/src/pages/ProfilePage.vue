<template>
    <div class="h-screen flex justify-center">
        <!-- Side Bar  -->
        <SideBar />
        <!-- Profile Side -->
        <div class="w-11/12 flex justify-center items-center text-white bg-slate-950">
            <div class="text-center w-1/2 p-5 my-5">
                <div class="flex justify-center">
                    <input @change="handleFileInputChange" class="hidden" type="file" ref="inputFile">
                    <img @click="updateProfileImage" class="rounded-full w-60 hov hover:opacity-80"
                        v-if="store.getters.getUserData.profileUrl" :src="store.getters.getUserData.profileUrl">

                </div>
                <div class="my-10 border-2 border-cyan-300 flex justify-around">
                    <div class="p-3 bg-black border-r-2 border-cyan-300 w-full">Chatrooms</div>
                    <div class="p-3 bg-black w-full">
                        Freinds</div>
                </div>
                <div class="my-10 text-xl flex justify-between">
                    <span class="text-right">User Name :</span>
                    <h1 class=" text-cyan-400" v-if="store.getters.getUserData">{{ store.getters.getUserData.name }}
                    </h1>
                </div>
                <div class="my-10 text-xl flex justify-between">
                    <span class="text-right">User Email :</span>
                    <h1 class=" text-cyan-400" v-if="store.getters.getUserData">{{ store.getters.getUserData.email }}
                    </h1>
                </div>
                <div class="my-10 text-xl flex justify-between">
                    <span class="text-right">User Description :</span>
                    <h1 class=" text-cyan-400">User Description</h1>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SideBar from '@/components/SideBar.vue'
import { ref } from 'vue'
import { useStore } from 'vuex'
import { config } from "../config/index"

export default {
    components: { SideBar },
    setup() {

        const store = useStore()
        const inputFile = ref(null)

        const updateProfileImage = () => {
            inputFile.value.click()
        }

        const handleFileInputChange = async (event) => {
            const file = event.target.files[0];

            // Cant be sent as json 
            const formData = new FormData();
            formData.append('image', file);

            const token = localStorage.getItem('token')
            const response = await fetch(`${config.apiUrl}user/image`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`, // Specify the content type if needed
                },
                body: formData
            })
            const imageFileName = await response.json()

            await store.dispatch("setUserData", { ...store.state.userData, profileUrl: `${config.profileImageUrl + imageFileName}` })
        }

        return { store: store, inputFile, updateProfileImage, handleFileInputChange, config }
    }
}

</script>

<style>
.hov {
    /* border: 0.5rem solid #58e6ff; */
    box-shadow: rgba(6, 24, 44, 0.4) 0px 0px 0px 2px, rgba(6, 24, 44, 0.65) 0px 4px 6px -1px, rgba(255, 255, 255, 0.08) 0px 1px 0px inset;
}

.hov:hover {
    border: 0.5rem solid #79fdff;
}
</style>