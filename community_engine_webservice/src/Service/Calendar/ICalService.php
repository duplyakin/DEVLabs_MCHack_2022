<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Calendar;


use App\Entity\Call;
use App\Entity\User;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ICalService
 * @package App\Service\Calendar
 */
class ICalService
{
    const DESCRIPTION = "Hi! We have created this event in your calendar to remind you that this week you have a meeting on Meetsup.\nWe have sent the contact details of your meeting partner to your email. Please write to the contact and agree on the date and time of the meeting.\n\nBest wishes,\nMeetsup team";
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * ICalService constructor.
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $parameterBag
    )
    {
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param Call $call
     * @param $body
     * @return string
     */
    public function createICalInstance(Call $call, $body)
    {
        $date = new \DateTime();
        $timeZone = $date->getTimezone();

        // 1. Create new calendar
        $vCalendar = new Calendar('-//Meetsup calendar//' . $this->parameterBag->get('default_host') . '//');
        $organizer = new Organizer('mailto:support@' . $this->parameterBag->get('default_host'), ['CN' => 'Meetsup']);

        // 2. Create an event
        $vEvent = new Event();
        $vEvent->setDtStart(date_time_set($call->getCallDate(), 20, 0, 0))
            ->setTimezoneString($timeZone->getName())
            ->setDtEnd(date_time_set($call->getCallDate(), 20, 30, 0))
            ->setNoTime(false)
//            ->setMsBusyStatus("FREE")
            ->setSummary('Meetsup Connect')
            ->setStatus(Event::STATUS_CONFIRMED)
            ->setTimeTransparency(Event::TIME_TRANSPARENCY_OPAQUE)
            ->setDescription($this->translator->trans(self::DESCRIPTION));
        /** @var User $user */
        foreach ($call->getUserObjects() as $user) {
            $vEvent->addAttendee('mailto:' . $user->getActualEmail(), [
                'CUTYPE' => 'INDIVIDUAL',
                'ROLE' => 'REQ-PARTICIPANT',
                'RSVP' => 'TRUE',
                'PARTSTAT' => 'NEEDS-ACTION',
                'X-NUM-GUESTS' => 0,
                'CN' => $user->getFullName(),
            ]);
        }
        $vEvent->setOrganizer($organizer)
            ->setDescriptionHTML(nl2br($this->translator->trans(self::DESCRIPTION)));


        // add some location information for apple devices
//        $vEvent->setLocation("Infinite Loop\nCupertino CA 95014", 'Infinite Loop', '37.332095,-122.030743');

        // 3. Add event to calendar
        $vCalendar->setMethod(Calendar::METHOD_REQUEST)
            ->addComponent($vEvent);

//        // 4. Set headers
//        header('Content-Type: text/calendar; charset=utf-8');
//        header('Content-Disposition: attachment; filename="cal3.ics"');
//
//// 5. Output
        return $vCalendar->render();
    }
}