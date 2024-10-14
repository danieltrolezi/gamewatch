<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use DateTime;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\CollectionReference as FirestoreCollection;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Firestore implements JsonSerializable
{
    public readonly string $id;
    public Timestamp $created_at;
    public Timestamp $updated_at;

    protected static ?string $collection = null;
    protected static array $conditions = [];
    protected static array $persist = [];
    protected static array $hidden = [];

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
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $attributes = get_object_vars($this);
        $attributes = $this->applyPersistFilter($attributes);
        $attributes = $this->applyHiddenFilter($attributes);

        return $attributes;
    }

    /**
     * @param array $attributes
     * @return array
     */
    private static function applyPersistFilter(array $attributes): array
    {
        $attributes = array_filter($attributes, function ($key) {
            return in_array($key, [...static::$persist, 'created_at', 'updated_at']);
        }, ARRAY_FILTER_USE_KEY);

        return $attributes;
    }

    /**
     * @param array $attributes
     * @return array
     */
    private static function applyHiddenFilter(array $attributes): array
    {
        $attributes = array_filter($attributes, function ($key) {
            return !in_array($key, static::$hidden);
        }, ARRAY_FILTER_USE_KEY);

        return $attributes;
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
            fn (DocumentSnapshot $snapshot) => self::makeModel($snapshot)
        );
    }

    /**
     * @param DocumentSnapshot $snapshot
     * @return static
     */
    private static function makeModel(DocumentSnapshot $snapshot): static
    {
        $model = static::class;
        $data = array_merge(
            ['id' => $snapshot->id()],
            $snapshot->data()
        );

        return new $model($data);
    }

    /**
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes): static
    {
        $attributes = self::prepareAttributes($attributes);
        $newDocument = self::getFirestoreCollection()->add($attributes);

        return self::makeModel(
            $newDocument->snapshot()
        );
    }

    /**
     * @param boolean $update
     * @return array
     */
    private static function prepareAttributes(array $attributes, bool $update = false): array
    {
        $attributes = self::applyPersistFilter($attributes);
        $attributes = self::setTimestamps($attributes, $update);

        return $attributes;
    }

    /**
     * @param array $attributes
     * @param boolean $update
     * @return array
     */
    private static function setTimestamps(array $attributes, bool $update = false): array
    {
        $now = new Timestamp(new DateTime());

        if ($update == false) {
            $attributes['created_at'] = $now;
        }

        $attributes['updated_at'] = $now;

        return $attributes;
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
     * @param string $id
     * @return self|null
     */
    public static function find(string $id): ?self
    {
        $snapshot = self::getFirestoreCollection()->document($id)->snapshot();

        if ($snapshot->exists()) {
            return self::makeModel($snapshot);
        }

        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @return static
     */
    public static function where(string $field, mixed $value, string $operator = '=='): static
    {
        if ($field == 'id') {
            $field = '__name__';
        }

        static::$conditions[] = compact('field', 'value', 'operator');
        return new static();
    }

    /**
     * @param string $field
     * @return static
     */
    public static function whereNotNull(string $field): static
    {
        return self::where($field, '', '!=');
    }

    /**
     * @return Query
     */
    private static function getQuery(): Query
    {
        $query = self::getFirestoreCollection();

        foreach (static::$conditions as $condition) {
            $query = $query->where(
                $condition['field'],
                $condition['operator'],
                $condition['value']
            );
        }

        static::$conditions = [];

        return $query;
    }

    /**
     * @return Collection
     */
    public static function get(): Collection
    {
        $documents = self::getQuery()->documents();

        return self::makeResultCollection(
            $documents->rows()
        );
    }

    /**
     * @return static|null
     */
    public static function first(): ?static
    {
        $documents = self::getQuery()->limit(1)->documents();

        if ($documents->isEmpty()) {
            return null;
        }

        return static::makeModel(
            $documents->rows()[0]
        );
    }

    /**
     * @return LazyCollection
     */
    public static function lazy(): LazyCollection
    {
        $documents = self::getQuery()->documents();

        return LazyCollection::make(function () use ($documents) {
            foreach ($documents as $document) {
                yield self::makeModel($document);
            }
        });
    }

    /**
     * @return self
     */
    public function update(array $attributes): self
    {
        if (empty($this->id)) {
            throw new \RuntimeException('Id is required for update.');
        }

        $document = $this->getFirestoreCollection()->document($this->id);

        if (!$document->snapshot()->exists()) {
            throw new NotFoundException();
        }

        $attributes = $this->prepareAttributes($attributes, true);
        $document->set($attributes, ['merge' => true]);
        $this->fill($attributes);

        return $this;
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
     * @return void
     */
    public function delete(): void
    {
        if (empty($this->id)) {
            throw new \RuntimeException('Id is required for delete.');
        }

        $this->getFirestoreCollection()
            ->document($this->id)
            ->delete();
    }
}
