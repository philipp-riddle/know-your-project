import axios from "axios";
import { useToastStore} from '@/stores/ToastStore';

export function useExceptionHandler() {
    const toastStore = useToastStore();

    const setupInterceptor = () => {
        axios.interceptors.response.use(
            (successResponse) => {
                // do nothing with the response data on success; simply return it
                return successResponse;
            },
            (error) => {
                if (!error) {
                    console.error('Response is a HTTP exception but error IS NULL!', error);
                    return;
                }

                // add the exception as a toast;
                // this makes it visible in the bottom left of the screen
                toastStore.addToast('error', error.response.data.message);

                return error;
            },
        );
    };

    return {
        setupInterceptor,
    }
}