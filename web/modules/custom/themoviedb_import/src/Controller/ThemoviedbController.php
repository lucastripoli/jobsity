<?php
namespace Drupal\themoviedb_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\GuzzleException;
use Masterminds\HTML5\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ThemoviedbController.
 *  to configure access '/admin/config/system/themoviedb'
 *
 *
 * @package Drupal\themoviedb_import\Controller
 *
 *
 */
class ThemoviedbController extends ControllerBase {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * string.
   *
   * @var string
   */
  protected $url;

  /**
   * string.
   *
   * @var string
   */
  protected $key;

  /**
   * string.
   *
   * @var string
   */
  protected $movie_url;

  /**
   * string.
   *
   * @var string
   */
  protected $actor_url;

  /**
   * string.
   *
   * @var string
   */
  protected $count;

  /**
   * string.
   *
   * @var string
   */
  protected $genre_url;

  /**
   * string.
   *
   * @var string
   */
  protected $language;

  /**
   * {@inheritdoc}
   */
  public function __construct( $http_client) {
    $configuration = \Drupal::config('themoviedb_import.settings');

    $this->httpClient = $http_client;
    $this->url = $configuration->get('themoviedb_api_url');
    $this->key = $configuration->get('themoviedb_api_key');
    $this->movie_url = $configuration->get('themoviedb_movie_url_part');
    $this->actor_url = $configuration->get('themoviedb_actor_url_part');
    $this->genre_url = $configuration->get('themoviedb_genre_url_part');
    $this->count = $configuration->get('themoviedb_count');
    $this->language = $configuration->get('themoviedb_language');

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }
  /**
   * http request callback.
   *
   * @param string $url_part
   *   The part url we want to fetch initializing with slash '/'.
   * @param string $data
   *   The parameters to use in the request.
   *
   * @return array
   *   A data from the return of the site.
   */
  public function request_get($url_part, $data = []){

    try{

      $url = new \GuzzleHttp\Psr7\Uri($this->url.$url_part);
      $url = $url->withQueryValue($url,'api_key',  $this->key);
      if(!empty($data)){
        foreach ($data as $item_key => $item_value) {
          $url = $url->withQueryValue($url, $item_key, $item_value);
        }
      }

      $request = null;

      $request = $this->httpClient->request('GET', $url, $data);

    }catch (Exception $ex){
      \Drupal::logger('themoviedb_import')->notice(print_r( $ex->GetMessage(), TRUE));
    } catch (GuzzleException $ex) {
      \Drupal::logger('themoviedb_import')->notice(print_r( $ex->GetMessage(), TRUE));
    }
    if(empty($request)){
      return null;
    }
    return json_decode($request->getBody(), true);

  }

  /**
   * An function to return all genre data from moviedb.
   *
   * @return array
   *   A render array used to show the Posts list.
   */

  public function genreImport(){

    $genre = $this->request_get($this->genre_url, ['language' => $this->language]);

    $genre_associative_array = [];

    foreach ($genre['genres'] as $genre_item){

      $genre_associative_array[$genre_item['id']] = $genre_item['name'];

    }

    return $genre_associative_array;
  }



  public function movieImport(){
    $i = $this->count;
    $data = [];

    while($i > 0){
      $movies_array = $this->request_get($this->movie_url, ['page' => $i, 'language' => $this->language]);
      if(!empty($movies_array['results'])){
        $data = array_merge($data, $movies_array['results'] );
      }
      $i--;
    }

    return $data;
  }

  public function actorImport(){
    $i = $this->count;
    $data = [];

    while($i > 0){
      $actor_array = $this->request_get($this->actor_url, ['page' => $i, 'language' => $this->language]);
      if(!empty($actor_array['results'])){
        $data = array_merge($data, $actor_array['results'] );
      }
      $i--;
    }

    return $data;
  }

  public function ImportMovieById($id){
    $movie = $this->request_get('/movie/'.$id, ['language' => $this->language]);
    return $movie;
  }
  public function ImportActorById($id){
    $actor = $this->request_get('/person/'.$id, ['language' => $this->language]);
    return $actor;
  }
  public function ImportActorImageById($id){
    $actor = $this->request_get('/person/'.$id.'/images', ['language' => $this->language]);
    return $actor;
  }


  public function test(){

    //$this->deleteAllData();

    //$this->ImportData();

//    kpr($this->actorImport());
//    kpr($this->ImportActorById('2093355'));
    kpr($this->ImportData());
//    kpr($this->ImportActorImageById('1946699'));

    return new JsonResponse([]);
  }

  public function ImportData(){
    $actors_import = $this->actorImport();

    foreach ($actors_import as $actor){
      $this->SaveActor($actor);
    }

    kpr($actors_import);
  }

  public function SaveMovie($movie){
    $movieData = $this->ImportMovieById($movie['id']);

    $genres = [];
    if(!empty($movieData['genres'])){
      foreach($movieData['genres'] as $genre){
        $genres[] = $genre['name'];
      }
    }

    $movie_data = [
      'type' => 'movie',
      'title' => $movieData['title'],
      'body' =>       [
        'value' => $movieData['overview'],
        'format' => filter_default_format(),
      ],
      'field_genre' => $genres,
      'field_movie_id' => $movieData['id'],
      'field_popularity' => $movieData['popularity'],
      'field_original_language' => $movieData['original_language'],
      'field_poster' => $movieData['poster_path'],
      'field_release_date' => $movieData['release_date'],
    ];

    if($this->MovieDrupalById($movieData['id']) !== null){
      unset($movie_data['type']);
      $node = Node::load($this->MovieDrupalById($movieData['id']));
      foreach ( $movie_data as $field => $values) {
        $node->set($field, $values);
      }
      try {
        $node->save();
        return $node->id();
      } catch (EntityStorageException $e) {
        \Drupal::logger('themoviedb_import')->notice(print_r( $e->GetMessage(), TRUE));
      }
    }else{
      $node = Node::create( $movie_data);
      try {
        $node->save();
        return $node->id();
      } catch (EntityStorageException $e) {
        \Drupal::logger('themoviedb_import')->notice(print_r( $e->GetMessage(), TRUE));
      }
    }


  }

  public function SaveActor($actor_array){
    $actorData = $this->ImportActorById($actor_array['id']);
    $actorImagesData = $this->ImportActorImageById($actor_array['id']);

    $photo_array = [];

    foreach($actorImagesData['profiles'] as $images){
      $photo_array[] =  $images['file_path'];
    }

    $moviesArray = [];
    foreach($actor_array['known_for'] as $movie){
      $moviesArray[] = ['target_id' => $this->SaveMovie($movie)];
    }

    $actor_data = [
      'title' => $actor_array['name'],
      'field_actor_id' => $actor_array['id'],
      'body' =>       [
        'value' => $actorData['biography'],
        'format' => filter_default_format(),
      ],
      'field_birthday' => $actorData['birthday'],
      'field_day_of_death' => $actorData['deathday'],
      'field_movies_related' => $moviesArray,
      'field_popularity' => $actor_array['popularity'],
      'field_website' => $actorData['homepage'],
      'field_photo' => $photo_array,
      'type' => 'actor'
    ];

    if($this->ActorDrupalById($actor_array['id']) !== null){
      unset($actor_data['type']);
      $node = Node::load($this->ActorDrupalById($actor_array['id']));
      foreach ($actor_data as $field => $values) {
        $node->set($field, $values);
      }
      try {
        $node->save();
        return $node->id();
      } catch (EntityStorageException $e) {
        \Drupal::logger('themoviedb_import')->notice(print_r( $e->GetMessage(), TRUE));
      }
    }else{
      $node = Node::create($actor_data);
      try {
        $node->save();
        return $node->id();
      } catch (EntityStorageException $e) {
        \Drupal::logger('themoviedb_import')->notice(print_r( $e->GetMessage(), TRUE));
      }
    }

  }

  public function deleteAllData(){
    $query = \Drupal::entityQuery('node');
    $query->accessCheck(FALSE);
    $entity_ids = $query->execute();

    foreach($entity_ids as $nid)
    {
      $node = Node::load($nid);
      $node->delete();
    }
  }

  public function ActorDrupalById($id){
    $query = \Drupal::entityQuery('node');
    $query->condition('field_actor_id', $id);
    $query->accessCheck(FALSE);
    $entity_ids = $query->execute();

    foreach($entity_ids as $nid)
    {
      $node = Node::load($nid);
      $node->delete();
    }
//    if(!empty($entity_ids)){
//      return array_shift($entity_ids);
//    }

    return null;

  }

  public function MovieDrupalById($id){
    $query = \Drupal::entityQuery('node');
    $query->condition('field_movie_id', $id);
    $query->accessCheck(FALSE);
    $entity_ids = $query->execute();


    foreach($entity_ids as $nid)
    {
      $node = Node::load($nid);
      $node->delete();
    }

//    if(!empty($entity_ids)){
//      return array_shift($entity_ids);
//    }
    return null;
  }



}
