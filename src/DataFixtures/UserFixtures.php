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
                $client->setNbrVersement(rand(5,24));
                $client->setDateDebut(new \DateTime("2020-".rand(1,12)."-".rand(1,31)));
                $client->setDateFin(new \DateTime("2021-".rand(1,12)."-".rand(1,31)));
                $client->setSuspendu("présent");
                $client->setUser($user);
                $manager->persist($client);

            }
            $manager->flush();
        }
    }
}
