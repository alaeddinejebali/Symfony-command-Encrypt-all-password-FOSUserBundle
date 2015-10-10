<?php
namespace FOS\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrypterMD5Command extends ContainerAwareCommand{
    protected function configure(){
        $this
            ->setName('crypter:motsDePasses')
            ->setDescription('Cryptage MD5 de tous les mots de passes deja enrigitres dans la BD.')
            ->addArgument(
                'limitQuery',
                InputArgument::OPTIONAL,
                'Nombre d\'enregistrement?'
            );
    }

    private function isMd5($md5 = ''){
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $nbrLoop = 0;
        $limitQuery = $input->getArgument('limitQuery');
        $em = $this->getContainer()->get('doctrine');
        $users = $em->getRepository('UsersBundle:Users')->findAll();
        $userManipulator = $this->getContainer()->get('fos_user.util.user_manipulator');
        foreach ($users as $user) {
            if ($limitQuery) {
                if ($nbrLoop >= $limitQuery) {
                    break;
                }
            }
            if (!$this->isMd5($user->getPassword())) {
                $output->write('Cryptage mot de passe pour ' . $user->getPrenom() . ' ' . strtoupper($user->getNom()) . ': ');
                $userManipulator->changePassword($user->getUsername(), md5($user->getPassword()));
                $output->writeln('Termine.');
                $nbrLoop++;
            }
        }
        $output->writeln('/*---------------------------------------------------------------------');
        if ($nbrLoop == 0) {
            $output->writeln('-----------       Tous les mots de passes sont cryptes      -----------');
        } else {
            $output->writeln('---------       Cryptage termine pour ' . $nbrLoop . ' utilisateurs      ----------');
        }
        $output->writeln('---------------------------------------------------------------------*/');
    }
}
