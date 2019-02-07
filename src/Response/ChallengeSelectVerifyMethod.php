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
 * ChallengeSelectVerifyMethod.
 *
 * @method mixed getMegaphone()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\User getUser()
 * @method Model\_Message[] get_Messages()
 * @method bool isMegaphone()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isUser()
 * @method bool is_Messages()
 * @method $this setMegaphone(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setUser(Model\User $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMegaphone()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetUser()
 * @method $this unset_Messages()
 */
class ChallengeSelectVerifyMethod extends Response
{
    const JSON_PROPERTY_MAP = [
        'step_name'        => '',
        'step_data'        => 'Model\StepData',
        'user_id'          => 'int',
        'nonce_code'       => '',
    ];

}