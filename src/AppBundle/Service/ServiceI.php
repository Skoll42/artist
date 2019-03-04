<?php

namespace AppBundle\Service;

/**
 * We use it only for auto complete
 */
interface ServiceI
{
    const TEXT_MANIPULATION = 'app:text_manipulation';

    const UPLOADER_FILE_NAMER_FULL_PATH = 'uploader.file_namer_full_path';
    const UPLOADER_DIRECTORY_NAMER_DATE = 'uploader.directory_namer_date';
    const UPLOADER_CUSTOM_FILE_SYSTEM_STORAGE = 'uploader.custom_file_system_storage';

    const EVENT_SUBSCRIBER_CHARGE = 'subscriber.event_charge';
    const EVENT_SUBSCRIBER_AUTHORIZE = 'subscriber.event_authorize';
    const EVENT_SUBSCRIBER_REJECTED = 'subscriber.event_rejected';
    const COMMUNICATION_SENDER = 'communication.sender';

    const EXPORT_STATISTIC_CSV = 'export:new_user_statistic';
}
