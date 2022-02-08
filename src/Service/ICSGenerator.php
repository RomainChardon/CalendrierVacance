<?php
namespace App\Service;

use DateInterval;
use Symfony\Component\Filesystem\Filesystem;

class ICSGenerator
{
    private $tmp;

    public function __construct($tmp){
        $this->tmp = $tmp;
    }
    public function getICS($dateDebut,$dateFin,$demiJournee = false)
    {
        $dateEnd = $dateFin->add(new DateInterval('P1D'));
        // Create the ics file
        $fs = new Filesystem();
        //temporary folder, it has to be writable
        $tmpFolder = $this->tmp;

        //the name of your file to attach
        $fileName = 'meeting.ics';
        
        if ($demiJournee == true){
            $icsContent = "
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:".$dateDebut->format('Ymd')."T".$dateDebut->format('His')."
DTEND:".$dateFin->format('Ymd')."T".$dateFin->format('His')."
ORGANIZER;CN=Adeo Informatique:mailto:adeo-informatique@gmail.com
UID:".rand(5, 1500)."
DESCRIPTION:"." Vacances du ".$dateDebut->format('d/m/Y H:i:s')." au ".$dateFin->format('d/m/Y H:i:s')."
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Vacances
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR"
;
    } else {
        $icsContent = "
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:".$dateDebut->format('Ymd')."
DTEND:".$dateEnd->format('Ymd')."
ORGANIZER;CN=Adeo Informatique:mailto:adeo-informatique@gmail.com
UID:".rand(5, 1500)."
DESCRIPTION:"." Vacances du ".$dateDebut->format('d/m/Y')." au ".$dateFin->format('d/m/Y')."
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Vacances
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR"
;
    }
    $icsFile = $fs->dumpFile($tmpFolder.$fileName, $icsContent);

    return $tmpFolder.$fileName;
    }
}