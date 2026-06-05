export interface NotificationData {
    title?: string;
    message?: string;
    [key: string]: unknown;
}

export interface Notification {
    id: string;
    type: string;
    notifiable_id: number;
    notifiable_type: string;
    data: string | NotificationData;
    read_at: string | null;
    created_at: string;
    updated_at: string;
}
