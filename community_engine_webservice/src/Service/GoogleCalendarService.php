<?php
/**
 * meetsup
 *
 * @author Alexander Demchenko <strong.barnaul@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;


/**
 * Class GoogleCalendarService
 * @package App\Service
 */
class GoogleCalendarService
{
    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * GoogleCalendarService constructor.
     * @param \Google_Client $client
     */
    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
    }
//
//    /**
//     * @param $refreshToken
//     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
//     */
//    protected function auth($refreshToken)
//    {
//        $token = $this->getGoogleClient()
//            ->getOAuth2Provider()
//            ->getAccessToken(new RefreshToken(), ['refresh_token' => $refreshToken]);
//    }
//
//    /**
//     * @return \KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface
//     */
//    private function getGoogleClient()
//    {
//        return $this->clientRegistry
//            // "google_main" is the key used in config/packages/knpu_oauth2_client.yaml
//            ->getClient('google_main');
//    }
    // 1//0cmZzKcBqdtXSCgYIARAAGAwSNwF-L9Ir9rQK-9aFs9dpfoYqiEMUNCQmViWfgGxXPQQQxxiNIpTuOfXJlYKr_DgqOvPlJaUGacA
    // 1//0cmZzKcBqdtXSCgYIARAAGAwSNwF-L9Ir9rQK-9aFs9dpfoYqiEMUNCQmViWfgGxXPQQQxxiNIpTuOfXJlYKr_DgqOvPlJaUGacA

    public function test()
    {
        $this->client->setAccessToken('ya29.a0AfH6SMBF4zXN-u6Cbgk8NmWaz-KB0XfDJyH7FoJLO8YtVyOMuZKxl6Erj6cIw0wvTZd5gb5PokDVD-F-wwN5GHBK0_OMdUeyYaUzzPudKk2TYRLPYG4UQPnlyvW1stsHrw_oiNc30ALs6hTP21ckoXcBVaWCkbvJr_4');
        $this->client->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
        $this->client->fetchAccessTokenWithRefreshToken('1//0cmZzKcBqdtXSCgYIARAAGAwSNwF-L9Ir9rQK-9aFs9dpfoYqiEMUNCQmViWfgGxXPQQQxxiNIpTuOfXJlYKr_DgqOvPlJaUGacA');

        //dd($this->client->getAccessToken());
        //        $service = new Google_Service_Books($client);
//        $optParams = array(
//            'filter' => 'free-ebooks',
//            'q' => 'Henry David Thoreau'
//        );
//        $results = $service->volumes->listVolumes($optParams);
//
//        foreach ($results->getItems() as $item) {
//            echo $item['volumeInfo']['title'], "<br /> \n";
//        }

        $service = new \Google_Service_Calendar($this->client);
        dd($service->calendarList->listCalendarList());
//        $calendarId = 'primary';
//        $optParams = array(
//            'maxResults' => 10,
//            'orderBy' => 'startTime',
//            'singleEvents' => true,
//            'timeMin' => date('c'),
//        );
//        $results = $service->events->listEvents($calendarId, $optParams);
//        $events = $results->getItems();
//
//        if (empty($events)) {
//            print "No upcoming events found.\n";
//        } else {
//            print "Upcoming events:\n";
//            foreach ($events as $event) {
//                $start = $event->start->dateTime;
//                if (empty($start)) {
//                    $start = $event->start->date;
//                }
//                printf("%s (%s)\n", $event->getSummary(), $start);
//            }
//        }
//
//        // Print the next 10 events on the user's calendar.
//        $calendarId = 'primary';
//        $optParams = array(
//            'maxResults' => 10,
//            'orderBy' => 'startTime',
//            'singleEvents' => true,
//            'timeMin' => date('c'),
//        );
//        $results = $service->events->listEvents($calendarId, $optParams);
//        $events = $results->getItems();
//        dd($events);
    }
}