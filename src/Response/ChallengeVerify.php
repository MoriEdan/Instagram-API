<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/7/19
 * Time: 8:50 PM
 */

namespace InstagramAPI\Response;


use InstagramAPI\Response;

/**
 * ChallengeVerify.
 *
 * @method mixed getMegaphone()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method int getUploadId()
 * @method mixed getXsharingNonces()
 * @method Model\_Message[] get_Messages()
 * @method bool isMegaphone()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isUploadId()
 * @method bool isXsharingNonces()
 * @method bool is_Messages()
 * @method $this setMegaphone(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setUploadId(int $value)
 * @method $this setXsharingNonces(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMegaphone()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetUploadId()
 * @method $this unsetXsharingNonces()
 * @method $this unset_Messages()
 */
class ChallengeVerify extends Response
{
    const JSON_PROPERTY_MAP = [
        'xsharing_nonces' => '',
        'upload_id'       => 'int',
    ];
}