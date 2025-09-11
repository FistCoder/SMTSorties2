<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Timezone;

class AppFixtures extends Fixture
{


    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {


        $this->addCities($manager);
        $this->addLocations($manager);
        $this->addCampuses($manager);
        $this->addUsers($manager);
        $this->addStates($manager);
        $this->addHangouts($manager);
    }

    public function addUsers(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $campusRepository = $manager->getRepository(Campus::class);
        $campuses = $campusRepository->findAll();
        $hangoutRepository = $manager->getRepository(Hangout::class);
        $hangouts = $hangoutRepository->findAll();

        $user = new User();
        $user ->setUsername('user35')
            ->setFirstname('user')
            ->setLastname('user')
            ->setEmail('user@user.com')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'user'))
            ->setPhone('0123456789')
            ->setActive(true)
            ->setCampus($faker->randomElement($campuses));

        $manager->persist($user);

        $admin = new User();
        $admin->setUsername('admin')
            ->setFirstname('admin')
            ->setLastname('admin')
            ->setEmail('admin@admin.com')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'))
            ->setPhone('0123456789')
            ->setActive(true)
            ->setCampus($faker->randomElement($campuses))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $util = new User();
        $util->setUsername('util')
            ->setFirstname('util')
            ->setLastname('util')
            ->setEmail('util@util.com')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'user'))
            ->setPhone('0123456789')
            ->setActive(true)
            ->setCampus($faker->randomElement($campuses));
         $manager->persist($util);

        $ghostUser = new User();
        $ghostUser
            ->setUsername('X')
            ->setFirstname('Ghost')
            ->setLastname('User')
            ->setEmail('ghostadmin@admin.com')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'))
            ->setPhone('0123456789')
            ->setActive(true)
            ->setCampus($faker->randomElement($campuses));

        $manager->persist($ghostUser);



        for ($i = 0; $i < 3; $i++) {
            $fakeUser = new User();

            $lastName = $faker->lastName();
            $fakeUser
                ->setLastname($lastName)
                ->setUsername($lastName.'ou')
                ->setFirstname($faker->firstName)
                ->setEmail($faker->email)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $faker->password))
                ->setPhone($faker->phoneNumber)
                ->setActive($faker->randomElement([true, false]))
                ->setCampus($faker->randomElement($campuses));
            $manager->persist($fakeUser);
        }
        $manager->flush();
    }

    public function addCampuses(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $cityRepository = $manager->getRepository(City::class);
        $cities = $cityRepository->findAll();

        foreach ($cities as $city) {
            $campus = new Campus();
            $campus->setName($city->getName(). '_Campus');

            $manager->persist($campus);
        }
        $manager->flush();
    }

    public function addCities(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        $ville=['Brest', 'Strasbourg', 'Portoveccio'];

        for ($i = 0; $i <= 2; $i++) {
            $city = new City();
            $city->setName($ville[$i])
                 ->setPostalCode($faker->postcode);
            $manager->persist($city);
        }
        $manager->flush();
    }

    public function addHangouts(ObjectManager $manager): void
    {
        $campusRepository = $manager->getRepository(Campus::class);
        $campuses = $campusRepository->findAll();

        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();

        $statesRepository = $manager->getRepository(State::class);
        $locationRepository = $manager->getRepository(Location::class);

        $states = $statesRepository->findAll();
        $locations = $locationRepository->findAll();

        $faker = Factory::create('fr_FR');

        $sorties = [
            "Danse de forêt",
            "Ramassage de cailloux",
            "Sortie plage",
            "Bowling",
            "Billard",
            "Soirée bar",
            "Balade en ville",
            "Cinéma",
            "Restaurant",
            "Concert",
            "Randonnée"
        ];



        for ($i = 0; $i <= 20; $i++) {

            $startingDateTime = $faker->dateTimeBetween('+2 days', '+2 months');
            $lenght = \DateTime::createFromFormat('H:i:s', $faker->time('H:i:s','05:00:00'));


            $hangout = new Hangout();
            $hangout
                ->setName($faker->randomElement($sorties))
                ->setDetail($faker->paragraph)
                ->setMaxParticipant($faker->numberBetween(3,15))
                ->setLength($lenght)
                ->setStartingDateTime($startingDateTime)
                ->setState($faker->randomElement($states))
                ->setLocation($faker->randomElement($locations))
                ->setCampus($faker->randomElement($campuses))
                ->setOrganizer($faker->randomElement($users))
                ->addSubscriberLst($faker->randomElement($users))
                ->addSubscriberLst($faker->randomElement($users));

            $lastSubmitDate = clone $startingDateTime;
            $lastSubmitDate->modify('-1 day');

            $hangout->setLastSubmitDate(clone $lastSubmitDate->modify('-1 day'));

            $manager->persist($hangout);
        }
        $manager->flush();
    }

    public function addLocations(ObjectManager $manager): void
    {
        $cityRepository = $manager->getRepository(City::class);
        $cities = $cityRepository->findAll();

        $faker = Factory::create('fr_FR');
        for ($i = 0; $i <= 10; $i++) {
            $location = new Location();
            $location->setName($faker->city . '_Location')
                ->setStreet($faker->streetAddress)
                ->setLatitude($faker->latitude)
                ->setLongitude($faker->longitude)
                ->setCity($faker->randomElement($cities));

            $manager->persist($location);
        }
        $manager->flush();
    }

    public function addStates(ObjectManager $manager): void
    {
        $states = ["CREATE","OPEN", "CLOSED", "IN_PROCESS", "FINISHED", "CANCELLED", "ARCHIVED"];

        foreach ($states as $state) {
            $s = new State();
            $s->setLabel($state);
            $manager->persist($s);
        }
        $manager->flush();
    }
}
