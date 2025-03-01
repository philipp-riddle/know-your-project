<?php

namespace App\Entity\Interface;

enum AccessContext: string
{
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case UPLOAD = 'upload';
    case DOWNLOAD = 'download';

    // we need an extra access context for project invitations; this is a read-only context and allows the invited user to read the foreign project
    case READ_PROJECT_INVITATION = 'read_invitation';
}