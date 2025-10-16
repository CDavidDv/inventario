<template>
    <div class="min-h-screen bg-gray-200 p-2">
        <div class="bg-white px-0 md:px-8 shadow-xl rounded-2xl p-2 pb-12">
            <div class="flex flex-col px-8">
                <h1 class="text-4xl font-thin mb-6">
                    <slot name="title" />
                </h1>    
                <slot />
            </div>
        </div>
    </div>
</template>
