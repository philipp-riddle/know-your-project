<?php

namespace App\Serializer;


enum SerializerContext: string
{
    case DEFAULT = 'default';

    // invitation context is used when serializing invitations;
    // allows the project to be kept in the data although the user is not yet in it
    case INVITATION = 'invitation';
}