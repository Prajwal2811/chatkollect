@include('admin.components.header')

<body
    x-data="{ page: 'inbox', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">

    <div class="flex h-screen overflow-hidden">

        @include('admin.components.sidebar')

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">

            <!-- Small Device Overlay -->
            <div :class="sidebarToggle ? 'block xl:hidden' : 'hidden'"
                class="fixed z-50 h-screen w-full bg-gray-900/50"></div>

            @include('admin.components.navbar')

            <!-- ===== Main Content Start ===== -->
            <main>
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
                    <!-- Breadcrumb Start -->
                    <div x-data="{ pageName: `Data Tables`}">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"></h2>
                            <nav>
                                <ol class="flex items-center gap-1.5">
                                    <li>
                                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                            href="index.html">
                                            Home
                                            <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </li>
                                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Breadcrumb End -->

                    <div class="space-y-5 sm:space-y-6">
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="px-5 py-4 sm:px-6 sm:py-5">
                                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                                    Organisations Management
                                </h3>
                            </div>
                            <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                                <!-- DataTable Two -->
                                <div x-data="dataTableTwo()"
                                    class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                                    <div
                                        class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-500 dark:text-gray-400"> Show </span>
                                            <div x-data="{ isOptionSelected: false }"
                                                class="relative z-20 bg-transparent">
                                                <select
                                                    class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                                    :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                                    @click="isOptionSelected = true"
                                                    @change="perPage = $event.target.value">
                                                    <option value="10"
                                                        class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                                        10
                                                    </option>
                                                    <option value="8"
                                                        class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                                        8
                                                    </option>
                                                    <option value="5"
                                                        class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                                        5
                                                    </option>
                                                </select>
                                                <span
                                                    class="absolute right-2 top-1/2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                                    <svg class="stroke-current" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165"
                                                            stroke="" stroke-width="1.2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <span class="text-gray-500 dark:text-gray-400"> entries </span>
                                        </div>

                                        <div class="relative">
                                            <button
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"
                                                        fill="" />
                                                </svg>
                                            </button>

                                            <input type="text" x-model="search" placeholder="Search..."
                                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
                                        </div>
                                    </div>

                                    <div class="max-w-full overflow-x-auto">
                                        <div class="min-w-[1102px]">
                                            <!-- table header start -->
                                            <div
                                                class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                                                <div
                                                    class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                                    <div class="flex w-full cursor-pointer items-center justify-between"
                                                        @click="sortBy('user')">
                                                        <p
                                                            class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                                            Company Name
                                                        </p>

                                                        <span class="flex flex-col gap-0.5">
                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                                    fill="" />
                                                            </svg>

                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                                    fill="" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                                    <div class="flex w-full cursor-pointer items-center justify-between"
                                                        @click="sortBy('position')">
                                                        <p
                                                            class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                                            State / City
                                                        </p>

                                                        <span class="flex flex-col gap-0.5">
                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                                    fill="" />
                                                            </svg>

                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                                    fill="" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                                    <div class="flex w-full cursor-pointer items-center justify-between"
                                                        @click="sortBy('office')">
                                                        <p
                                                            class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                                             Email
                                                        </p>

                                                        <span class="flex flex-col gap-0.5">
                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                                    fill="" />
                                                            </svg>

                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                                    fill="" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                 
                                                
                                                <div
                                                    class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                                    <div class="flex w-full cursor-pointer items-center justify-between"
                                                        @click="sortBy('salary')">
                                                        <p
                                                            class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                                            Status
                                                        </p>

                                                        <span class="flex flex-col gap-0.5">
                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                                    fill="" />
                                                            </svg>

                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                                    fill="" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                                    <div
                                                        class="flex w-full cursor-pointer items-center justify-between">
                                                        <p
                                                            class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                                            Action
                                                        </p>

                                                        <span class="flex flex-col gap-0.5">
                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                                    fill="" />
                                                            </svg>

                                                            <svg class="fill-gray-300 dark:fill-gray-700" width="8"
                                                                height="5" viewBox="0 0 8 5" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                                    fill="" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- table header end -->

                                            <!-- table body start -->
                                            <template x-for="person in paginatedData" :key="person.id">
                                                <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">

                                                    <!-- Company Name -->
                                                    <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-4 dark:border-gray-800">
                                                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                                                            x-text="person.name"></p>
                                                    </div>

                                                    <!-- State / City -->
                                                    <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-4 dark:border-gray-800">
                                                        <p class="text-theme-sm text-gray-700 dark:text-gray-400"
                                                            x-text="person.position"></p>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-4 dark:border-gray-800">
                                                        <p class="text-theme-sm text-gray-700 dark:text-gray-400"
                                                            x-text="person.office"></p>
                                                    </div>

                                                    <!-- Status -->
                                                    <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-4 dark:border-gray-800">
                                                        <span
                                                            class="rounded-full px-3 py-1 text-xs font-medium"
                                                            :class="{
                                                                'bg-green-100 text-green-700': person.age === 'Active',
                                                                'bg-yellow-100 text-yellow-700': person.age === 'Pending',
                                                                'bg-red-100 text-red-700': person.age === 'Inactive'
                                                            }"
                                                            x-text="person.age">
                                                        </span>
                                                    </div>

                                                    <!-- Action -->
                                                    <div class="col-span-1 flex items-center justify-center px-4 py-4">
                                                        <div class="flex items-center gap-2">

                                                            <button
                                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">
                                                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M17.414 2.586a2 2 0 010 2.828L8.828 14H6v-2.828l8.586-8.586a2 2 0 012.828 0z">
                                                                    </path>
                                                                </svg>
                                                            </button>

                                                            <button
                                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                                                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M6 2a1 1 0 00-1 1v1H3a1 1 0 000 2h1v9a2 2 0 002 2h8a2 2 0 002-2V6h1a1 1 0 100-2h-2V3a1 1 0 00-1-1H6z">
                                                                    </path>
                                                                </svg>
                                                            </button>

                                                        </div>
                                                    </div>

                                                </div>
                                            </template>
                                            <!-- table body end -->
                                        </div>
                                    </div>

                                    <!-- Pagination Controls -->
                                    <div class="border-t border-gray-100 py-4 pl-[18px] pr-4 dark:border-gray-800">
                                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                                            <div
                                                class="flex items-center justify-center gap-0.5 pb-4 xl:justify-normal xl:pt-0">
                                                <button @click="prevPage()"
                                                    class="mr-2.5 flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                                                    :disabled="currentPage === 1">
                                                    Previous
                                                </button>

                                                <button @click="goToPage(1)"
                                                    :class="currentPage === 1 ? 'bg-blue-500/[0.08] text-brand-500' : 'text-gray-700 dark:text-gray-400'"
                                                    class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">
                                                    1
                                                </button>

                                                <template x-if="currentPage > 3">
                                                    <span
                                                        class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">...</span>
                                                </template>

                                                <template x-for="page in pagesAroundCurrent" :key="page">
                                                    <button @click="goToPage(page)"
                                                        :class="currentPage === page ? 'bg-blue-500/[0.08] text-brand-500' : 'text-gray-700 dark:text-gray-400'"
                                                        class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">
                                                        <span x-text="page"></span>
                                                    </button>
                                                </template>

                                                <template x-if="currentPage < totalPages - 2">
                                                    <span
                                                        class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium text-gray-700 hover:bg-blue-500/[0.08] hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500">...</span>
                                                </template>

                                                <button @click="nextPage()"
                                                    class="ml-2.5 flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                                                    :disabled="currentPage === totalPages">
                                                    Next
                                                </button>
                                            </div>

                                            <p
                                                class="border-t border-gray-100 pt-3 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400 xl:border-t-0 xl:pt-0 xl:text-left">
                                                Showing <span x-text="startEntry"></span> to
                                                <span x-text="endEntry"></span> of
                                                <span x-text="totalEntries"></span> entries
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                 <script>
                                    function dataTableTwo() {
                                        return {
                                            search: "",
                                            sortColumn: "name",
                                            sortDirection: "asc",
                                            currentPage: 1,
                                            perPage: 10,

                                            data: [
                                                {
                                                    id: 1,
                                                    name: "ABC Technologies Pvt Ltd",
                                                    position: "Maharashtra / Pune",
                                                    office: "info@abctech.com",
                                                    age: "Active",
                                                },
                                                {
                                                    id: 2,
                                                    name: "Global Solutions Ltd",
                                                    position: "Delhi / New Delhi",
                                                    office: "contact@globalsolutions.com",
                                                    age: "Pending",
                                                },
                                                {
                                                    id: 3,
                                                    name: "TechNova Systems",
                                                    position: "Karnataka / Bangalore",
                                                    office: "admin@technova.com",
                                                    age: "Inactive",
                                                },
                                                {
                                                    id: 4,
                                                    name: "Prime Logistics",
                                                    position: "Gujarat / Ahmedabad",
                                                    office: "support@primelogistics.com",
                                                    age: "Active",
                                                },
                                                {
                                                    id: 5,
                                                    name: "Future Innovations",
                                                    position: "Telangana / Hyderabad",
                                                    office: "hello@futureinnovations.com",
                                                    age: "Pending",
                                                }
                                            ],

                                            get pagesAroundCurrent() {
                                                let pages = [];
                                                const startPage = Math.max(2, this.currentPage - 2);
                                                const endPage = Math.min(this.totalPages - 1, this.currentPage + 2);

                                                for (let i = startPage; i <= endPage; i++) {
                                                    pages.push(i);
                                                }

                                                return pages;
                                            },

                                            get filteredData() {
                                                const searchLower = this.search.toLowerCase();

                                                return this.data
                                                    .filter(person =>
                                                        person.name.toLowerCase().includes(searchLower) ||
                                                        person.position.toLowerCase().includes(searchLower) ||
                                                        person.office.toLowerCase().includes(searchLower)
                                                    )
                                                    .sort((a, b) => {
                                                        let modifier = this.sortDirection === "asc" ? 1 : -1;

                                                        if (a[this.sortColumn] < b[this.sortColumn]) return -1 * modifier;
                                                        if (a[this.sortColumn] > b[this.sortColumn]) return 1 * modifier;

                                                        return 0;
                                                    });
                                            },

                                            get paginatedData() {
                                                const start = (this.currentPage - 1) * parseInt(this.perPage);
                                                const end = start + parseInt(this.perPage);

                                                return this.filteredData.slice(start, end);
                                            },

                                            get totalEntries() {
                                                return this.filteredData.length;
                                            },

                                            get startEntry() {
                                                if (this.totalEntries === 0) return 0;
                                                return (this.currentPage - 1) * parseInt(this.perPage) + 1;
                                            },

                                            get endEntry() {
                                                const end = this.currentPage * parseInt(this.perPage);
                                                return end > this.totalEntries ? this.totalEntries : end;
                                            },

                                            get totalPages() {
                                                return Math.max(
                                                    1,
                                                    Math.ceil(this.filteredData.length / parseInt(this.perPage))
                                                );
                                            },

                                            goToPage(page) {
                                                if (page >= 1 && page <= this.totalPages) {
                                                    this.currentPage = page;
                                                }
                                            },

                                            nextPage() {
                                                if (this.currentPage < this.totalPages) {
                                                    this.currentPage++;
                                                }
                                            },

                                            prevPage() {
                                                if (this.currentPage > 1) {
                                                    this.currentPage--;
                                                }
                                            },

                                            sortBy(column) {
                                                if (this.sortColumn === column) {
                                                    this.sortDirection =
                                                        this.sortDirection === "asc" ? "desc" : "asc";
                                                } else {
                                                    this.sortColumn = column;
                                                    this.sortDirection = "asc";
                                                }
                                            }
                                        };
                                    }
                                    </script>
                                <!-- DataTable Two -->
                            </div>
                        </div>

                       
                    </div>
                </div>
            </main>
            <!-- ===== Main Content End ===== -->

        </div>
        <!-- ===== Content Area End ===== -->

    </div>

    @include('admin.components.footer')
</body>