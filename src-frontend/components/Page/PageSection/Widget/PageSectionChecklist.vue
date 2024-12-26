<template>
    <div class="row">
        <div class="col-sm-12 col-lg-4 card" v-if="checklist">
            <div class="card-body p-2">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h4 class="mb-2">
                        <input
                            class="p bold form-control magic-input"
                            type="text"
                            v-model="checklist.name"
                            placeholder="Enter checklist name"
                            @input="onChecklistRename"
                        />
                    </h4>
                    <div v-if="pageSection" v-tooltip="areAllItemsCompleted ? 'All items are completed' : 'Not all items are completed'">
                        <div v-if="areAllItemsCompleted">
                            <span class="badge rounded-pill bg-primary">
                                <span>{{ completedChecklistItems }} / {{ checklist.pageSectionChecklistItems.length }}</span>
                            </span>
                        </div>
                        <div v-else>
                            <span class="badge rounded-pill bg-danger">
                                {{ completedChecklistItems }} / {{ checklist.pageSectionChecklistItems.length }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <div v-for="item in checklist.pageSectionChecklistItems" :key="item.id">
                        <div class="d-flex justify-content-between align-items-center">
                            <PageSectionChecklistItem :item="item" :onItemUpdate="onChecklistUpdateItem" :focusOnInit="false" />
                            <div class="dropdown">
                                <h5 class="dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                                    <font-awesome-icon :icon="['fas', 'ellipsis']" />
                                </h5>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><span class="dropdown-item" href="#" @click.stop="onChecklistDeleteItem(item)">Delete</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 ps-3">
                        <PageSectionChecklistItem :onItemEnter="onChecklistAddItem" :resetOnUpdate="true" :displayCompleteInput="false" />
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageSectionChecklistItem from '@/components/Page/PageSection/Widget/PageSectionChecklistItem.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { usePageSectionChecklistItemStore } from '@/stores/PageSectionChecklistItemStore.js';
    import { computed, ref, onMounted, nextTick } from 'vue';
    import { useDebounceFn } from '@vueuse/core'

    const props = defineProps({
        pageSection: { // this prop is only set if we have an already existing section
            type: Object,
            required: false,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
    });

    // this will be used when resetting the component
    const defaultChecklistValue = {
        name: 'Checklist',
        pageSectionChecklistItems: [],
    };
    const checklist = ref(props?.pageSection?.pageSectionChecklist);
    const pageSectionId = ref(props?.pageSection?.id);
    const checklistId = ref(checklist.value?.id);
    const completedChecklistItems = computed(() => {
        return checklist.value.pageSectionChecklistItems.filter((item) => item.complete).length;
    });
    const areAllItemsCompleted = computed(() => {
        return completedChecklistItems.value == checklist.value.pageSectionChecklistItems.length;
    });
    const debouncedPageSectionChecklistUpdate = useDebounceFn(() => {
        props.onPageSectionSubmit({
            pageSectionChecklist: {
                name: checklist.value.name,
            },
        });
    }, 1000);
    const pageSectionStore = usePageSectionStore();
    const pageSectionChecklistItemStore = usePageSectionChecklistItemStore();

    const onChecklistAddItem = (item) => {
        // if the checklist item (i.e. the pageSection) already exists we only need to add a nwew checklist item 
        if (pageSectionId.value) {
            const createdChecklistItem = {
                ...item,
                pageSectionChecklist: checklist.value.id,
            };

            pageSectionChecklistItemStore.createChecklistItem(pageSectionId.value, createdChecklistItem);
        } else {
            // create entirely new checklist
            checklist.value = {
                ...checklist.value,
                pageSectionChecklistItems: [
                    {...item} // this causes the item to deconstruct and makes sure all values are copied
                ],
            };

            props.onPageSectionSubmit({
                pageSectionChecklist: checklist.value,
            }).then((createdSection) => {
                pageSectionId.value = createdSection.id;
                checklist.value.id = createdSection.pageSectionChecklist.id;
            });
        }
    };

    const onChecklistDeleteItem = async (checklistItem) => {
        await pageSectionChecklistItemStore.deleteChecklistItem(props.pageSection.id, checklistItem);
    };

    const onChecklistUpdateItem = (item) => {
        if (props.pageSection) {
            pageSectionChecklistItemStore.updateChecklistItem(props.pageSection.id, item);
        } else {
            console.error('No pageSection provided in component; want to update checklist item');
        }
    };

    const onChecklistRename = async () => {
        if (props.pageSection) {
            await debouncedPageSectionChecklistUpdate();
        } else {
            console.error('No pageSection provided in component; want to rename checklist');
        }
    };
</script>