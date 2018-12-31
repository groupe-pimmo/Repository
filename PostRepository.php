<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function recupId($request){
      $var=$request->query->get('form');
      $res = $this->findByUserId($var);
      return $res[0]['user_id'];
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function verifUserId($userid){
      //je suis bloqué sur cette fonction, son but : quand au moment de la connexion le client retre un userId qui n'existe pas, cette fonction retourn 0 mais je ne sais pas comment et-ce que la requete ne bloque pas tout si le userId n'existe pas
      $conn = $this->getEntityManager()->getConnection();

      $sql = '
            SELECT * FROM client c
            WHERE c.user_id = :userid
            ';
      $stmt = $conn->prepare($sql);
      $stmt->execute(['userid' => $userid]);
      $res = $stmt->fetchAll();
      if ($res[0]['user_id'] == $userid){
        return 1;
      }
      else {
        return 0;
      }
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function findByUserId($user_id): array //pas de condition : si oui ou non reservation confirmee
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM post p JOIN client c JOIN professionnel pr
            WHERE p.user_id = :userid and c.user_id = :userid and c.cp = pr.cp and p.prestation = pr.prestation and p.type = pr.type
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $user_id['id']]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function findForPanier($user_id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM post p JOIN client c JOIN professionnel pr
            WHERE p.confirmee = 0 and p.user_id = :userid and c.user_id = :userid and c.cp = pr.cp and p.prestation = pr.prestation and p.type = pr.type
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $user_id['id']]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function findForMesRes($user_id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM post p JOIN client c JOIN professionnel pr
            WHERE p.confirmee = 1 and p.user_id = :userid and c.user_id = :userid and c.cp = pr.cp and p.prestation = pr.prestation and p.type = pr.type
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $user_id['id']]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    public function supprRes($id_res){

      $conn = $this->getEntityManager()->getConnection();

        $sql = '
            DELETE FROM post p WHERE p.id = :idpost
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['idpost' => $id_res]);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*


  SELECT * FROM post p NATURAL JOIN client c NATURAL JOIN professionnel pr
            WHERE p.user_id = :userid and c.user_id = :userid and c.cp = pr.cp and p.prestation = pr.prestation

    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
