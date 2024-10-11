<?php

namespace App\Models;

use App\Exceptions\NotFoundHttpException;
use DateTime;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\CollectionReference as FirestoreCollection;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Firestore
{
    public readonly string $id;
    public Timestamp $created_at;
    public Timestamp $updated_at;

    protected static ?string $collection = null;
    protected static array $persist = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function fill(array $attributes = []): void
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @return FirestoreClient
     */
    protected static function getFirestoreClient(): FirestoreClient
    {
        return resolve(FirestoreClient::class);
    }

    /**
     * @return string
     */
    protected static function getDefaultCollectionName(): string
    {
        if (!empty(static::$collection)) {
            return static::$collection;
        }

        $className = (new \ReflectionClass(static::class))->getShortName();
        return Str::snake($className) . 's';
    }

    /**
     * @param string|null $collection
     * @return FirestoreCollection
     */
    private static function getFirestoreCollection(): FirestoreCollection
    {
        $collectionName = self::getDefaultCollectionName();
        return self::getFirestoreClient()->collection($collectionName);
    }

    /**
     * @param DocumentSnapshot[] $rows
     * @return Collection
     */
    private static function makeResultCollection(array $rows): Collection
    {
        return collect($rows)->map(
            fn (DocumentSnapshot $snapshot) => self::newModel($snapshot)
        );
    }

    /**
     * @param array $attributes
     * @return self
     */
    private static function newModel(DocumentSnapshot $snapshot): self
    {
        $model = static::class;
        $data = array_merge(
            ['id' => $snapshot->id()],
            $snapshot->data()
        );

        return new $model($data);
    }

     /**
     * @return array
     */
    public static function all(): Collection
    {
        $collection = self::getFirestoreCollection();
        $documents = $collection->documents();

        return self::makeResultCollection(
            $documents->rows()
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @param string|null $collection
     * @return Collection
     */
    public static function where(string $field, mixed $value, string $operator = '='): Collection
    {
        $query = self::getFirestoreCollection()->where($field, $operator, $value);
        $documents = $query->documents();

        return self::makeResultCollection(
            $documents->rows()
        );
    }

    /**
     * @param array[] $conditions
     * @return Collection
     */
    public static function whereMany(array $conditions): Collection
    {
        $query = self::getFirestoreCollection();

        foreach ($conditions as $condition) {
            list($field, $operator, $value) = $condition;
            $query = $query->where($field, $operator, $value);
        }

        $documents = $query->documents();

        return self::makeResultCollection(
            $documents->rows()
        );
    }

    /**
     * @param string $id
     * @return self|null
     */
    public static function find(string $id): ?self
    {
        $snapshot = self::getFirestoreCollection()->document($id)->snapshot();

        if ($snapshot->exists()) {
            return self::newModel($snapshot);
        }

        return null;
    }

    /**
     * @param array $attributes
     * @return self
     */
    public static function create(array $attributes): self
    {
        $attributes = self::prepareAttributes($attributes);
        $newDocument = self::getFirestoreCollection()->add($attributes);

        return self::newModel(
            $newDocument->snapshot()
        );
    }

    /**
     * @param boolean $update
     * @return array
     */
    private static function prepareAttributes(array $attributes, bool $update = false): array
    {
        $attributes = array_filter($attributes, function ($key) {
            return in_array($key, static::$persist);
        }, ARRAY_FILTER_USE_KEY);

        self::setTimesmaps($attributes, $update);

        return $attributes;
    }

    /**
     * @param array $data
     * @param boolean $update
     * @return void
     */
    private static function setTimesmaps(array &$attributes, bool $update = false): void
    {
        $now = new Timestamp(new DateTime());

        if ($update == false) {
            $attributes['created_at'] = $now;
        }

        $attributes['updated_at'] = $now;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $data = get_object_vars($this);

        if (!empty($this->id)) {
            return $this->update($data);
        }

        return $this->create($data);
    }

    /**
     * @return self
     */
    public function update(array $attributes): self
    {
        if (empty($this->id)) {
            throw new \RuntimeException('Id is required for update');
        }

        $document = $this->getFirestoreCollection()->document($this->id);

        if (!$document->snapshot()->exists()) {
            throw new NotFoundHttpException();
        }

        $attributes = $this->prepareAttributes($attributes, true);
        $document->set($attributes, ['merge' => true]);
        $this->fill($attributes);

        return $this;
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        $this->getFirestoreCollection()->document($this->id)->delete();
    }
}
