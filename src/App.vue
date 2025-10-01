<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';

const nameList = ref('');
const isDrawing = ref(false);
const showVideo = ref(false);
const showResult = ref(false);
const videoFadeOut = ref(false);
const selectedName = ref('');
const videoElement = ref(null);
const audioElement = ref(null);
const isMuted = ref(true);

const names = computed(() => {
    return nameList.value
        .split('\n')
        .map(name => name.trim())
        .filter(name => name.length > 0);
});

const canDraw = computed(() => names.value.length > 0);

const handleTimeUpdate = () => {
    if (videoElement.value) {
        const timeRemaining = videoElement.value.duration - videoElement.value.currentTime;
        if (timeRemaining <= 1 && !videoFadeOut.value) {
            videoFadeOut.value = true;
        }
    }
};

const toggleAudio = () => {
    if (audioElement.value) {
        if (isMuted.value) {
            audioElement.value.play();
            isMuted.value = false;
        } else {
            audioElement.value.pause();
            isMuted.value = true;
        }
    }
};

const tryAutoPlay = async () => {
    if (audioElement.value && isMuted.value) {
        try {
            await audioElement.value.play();
            isMuted.value = false;
        } catch (error) {
            console.log('Autoplay prevented:', error);
        }
    }
};

onMounted(async () => {
    await nextTick();
    tryAutoPlay();

    const handleFirstInteraction = () => {
        tryAutoPlay();
        document.removeEventListener('click', handleFirstInteraction);
        document.removeEventListener('keydown', handleFirstInteraction);
    };

    document.addEventListener('click', handleFirstInteraction);
    document.addEventListener('keydown', handleFirstInteraction);
});

const startDraw = () => {
    if (!canDraw.value || isDrawing.value) return;

    const randomIndex = Math.floor(Math.random() * names.value.length);
    selectedName.value = names.value[randomIndex];

    isDrawing.value = true;
    showVideo.value = true;
    showResult.value = false;
    videoFadeOut.value = false;

    setTimeout(() => {
        if (videoElement.value) {
            videoElement.value.play();
        }
    }, 100);
};

const handleVideoEnded = () => {
    setTimeout(() => {
        showResult.value = true;
    }, 500);
};

const skipVideo = () => {
    if (videoElement.value) {
        videoElement.value.pause();
    }
    videoFadeOut.value = true;
    setTimeout(() => {
        showResult.value = true;
    }, 500);
};

const reset = () => {
    isDrawing.value = false;
    showVideo.value = false;
    showResult.value = false;
    videoFadeOut.value = false;
    selectedName.value = '';
};
</script>

<template>
    <!-- Background Music -->
    <audio ref="audioElement" loop>
        <source src="/duel-bgm.mp3" type="audio/mpeg" />
    </audio>

    <div class="min-h-screen bg-white text-black flex items-center justify-center p-8">
        <!-- Main Input Area -->
        <div v-if="!isDrawing" class="w-full max-w-2xl space-y-6">
            <div class="flex items-center justify-center gap-4 mb-8">
                <h1 class="text-4xl font-light text-center">決鬥吧 遊戲 Boy !</h1>
                <button
                    @click="toggleAudio"
                    class="px-3 py-2 text-sm hover:bg-gray-100 transition-colors"
                    :title="isMuted ? '播放音樂' : '暫停音樂'"
                >
                    {{ isMuted ? '🔊' : '🔇' }}
                </button>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-medium">
                    輸入名單（每行一個名字）
                </label>
                <textarea
                    v-model="nameList"
                    rows="12"
                    class="w-full px-4 py-3 border-2 border-black focus:outline-none focus:ring-0 font-mono text-base resize-none"
                    placeholder="Alice&#10;Bob&#10;Charlie"
                />
                <div class="text-sm text-gray-600">
                    共 {{ names.length }} 個名字
                </div>
            </div>

            <button
                @click="startDraw"
                :disabled="!canDraw"
                class="w-full py-4 px-8 bg-black text-white font-medium text-lg hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
                抽卡
            </button>
        </div>

        <!-- Video Overlay -->
        <div
            v-if="showVideo"
            class="fixed inset-0 z-50 bg-black flex items-center justify-center"
        >
            <video
                ref="videoElement"
                @ended="handleVideoEnded"
                @timeupdate="handleTimeUpdate"
                class="w-full h-full object-contain"
                muted
            >
                <source src="/doll-animation.mp4" type="video/mp4" />
            </video>

            <!-- Skip Button -->
            <button
                v-if="!showResult"
                @click="skipVideo"
                class="absolute bottom-8 right-8 px-6 py-2 text-gray-600 hover:bg-white hover:text-black transition-colors font-medium"
            >
                Skip
            </button>

            <!-- White fade overlay -->
            <div
                v-if="videoFadeOut"
                class="absolute inset-0 bg-white video-fade-overlay"
            />

            <!-- Result Display -->
            <div
                v-if="showResult"
                class="absolute inset-0 flex flex-col items-center justify-center bg-white animate-fade-in"
            >
                <div class="text-center space-y-6">
                    <h2 class="text-6xl font-light tracking-wide">
                        {{ selectedName }}
                    </h2>
                    <button
                        @click="reset"
                        class="mt-12 px-8 py-3 border-2 border-black hover:bg-black hover:text-white transition-colors"
                    >
                        返回
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-fade-overlay {
    animation: videoFadeIn 1s ease-in-out forwards;
}

@keyframes videoFadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
</style>
