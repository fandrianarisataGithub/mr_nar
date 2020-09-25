<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setPassword($this->passwordEncoder->encodePassword($user, "password" . $i));
            $user->setUsernmane('username' . $i);
            $user->setNom($faker->name);
            $user->setPrenom($faker->lastName);
            $user->setAdresse($faker->address);
            $user->setType("editor");
            $user->setCin("12345678912".$i);
            $user->setPhone("0332105406".$i);
            $manager->persist($user);
            // add client
            for($j=1; $j<=30; $j++){
                $client = new Client();
                $client->setMatricule("mat".$i.$j);
                $client->setNom($faker->name);
                $client->setPrenom($faker->lastName);
                $n = rand(213 , 5846);

                $client->setCin($n."2".$i.$j.$i);
                $client->setImage1($faker->imageUrl(640,480));
                $client->setImage2($faker->imageUrl(640, 480));
                $client->setAdresse($faker->address);
                $client->setMontantMensuel(rand(100,1200));
                $client->setMontant(rand(10000,120000));
                $client->setNbrVersement(rand(5,36));
                $client->setEtatClient('présent');
                $client->setCreatedAt($faker->dateTimeBetween('-3 years', 'now'));
                $client->setVerifier("non");
                $client->setNumeroBl(112458);
                $client->setDateDebut(new \DateTime("2020-".rand(1,12)."-".rand(1,29)));
                // dateFin
                // calcul de la date 
                $dd = $client->getDateDebut();
                // consvertissena hon string lo zany
                $dd_s = $dd->format('Y-m-d');
                // $dd = date($dd);
                // nombre de versement
                $nbr = $client->getNbrVersement();
                $nbr--;
                // date de fin en string

                $df = date("Y-m-d", strtotime("$dd_s + $nbr month"));

                $df_dt = new \DateTime($df);
                $client->setDateFin($df_dt);
                $tab_pointage = $this->tab_mois($client);
                $client->setTabPointage($tab_pointage);
                $client->setNumeroPointage(-1);

                $client->setUser($user);
                $manager->persist($client);

            }
            $manager->flush();
        }
    }
    public function tab_mois(Client $client)
    {
        $nbr_versement = $client->getNbrVersement();
        $date_debut = $client->getDateDebut();
        
        $date_debut_s = $date_debut->format("d-m-Y");
        $tab_date_complet = [$date_debut_s];
        $tab_pointage = [];
        $tab_pointage_s="";
        for($i = 1; $i<= $nbr_versement; $i++){
            $date_debut->add(new \DateInterval('P1M'));
            $date_s = $date_debut->format("d-m-Y");
            array_push($tab_date_complet, $date_s);
        }

        for($i = 0; $i< count($tab_date_complet); $i++){
           $t = explode("-", $tab_date_complet[$i]);
           $mois = $t[1]."-".$t[2];
           array_push($tab_pointage, $mois);
        }

        for($i = 0; $i< count($tab_pointage); $i++){
           $mois = $tab_pointage[$i];
            $tab_pointage_s .= $mois."__";
            if($i== count($tab_pointage)-1){
                $tab_pointage_s .= $mois;
            }
         }

        return $tab_pointage_s;

    }
}

