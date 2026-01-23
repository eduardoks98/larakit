<?php

namespace Eduardoks98\WhatsAppOfficial\Enums;

/**
 * WhatsApp Message Type Enum
 *
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages
 */
enum MessageType: string
{
    /**
     * Text message
     */
    case TEXT = 'text';

    /**
     * Image message
     */
    case IMAGE = 'image';

    /**
     * Video message
     */
    case VIDEO = 'video';

    /**
     * Audio message
     */
    case AUDIO = 'audio';

    /**
     * Document message (PDF, etc)
     */
    case DOCUMENT = 'document';

    /**
     * Sticker message
     */
    case STICKER = 'sticker';

    /**
     * Location message
     */
    case LOCATION = 'location';

    /**
     * Contact message
     */
    case CONTACTS = 'contacts';

    /**
     * Template message (pre-approved)
     */
    case TEMPLATE = 'template';

    /**
     * Interactive message (buttons, lists)
     */
    case INTERACTIVE = 'interactive';

    /**
     * Check if type supports media
     *
     * @return bool
     */
    public function supportsMedia(): bool
    {
        return in_array($this, [
            self::IMAGE,
            self::VIDEO,
            self::AUDIO,
            self::DOCUMENT,
            self::STICKER,
        ]);
    }

    /**
     * Check if type requires template approval
     *
     * @return bool
     */
    public function requiresApproval(): bool
    {
        return $this === self::TEMPLATE;
    }
}
