<?php

namespace Tests\Utils;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreTestUtils
{
    protected FirestoreClient $firestore;

    public function __construct()
    {
        $this->firestore = app()->make(FirestoreClient::class);
    }

    public function clearData(): void
    {
        $collections = ['users'];

        foreach ($collections as $collection) {
            $documents = $this->firestore->collection($collection)->documents();
            foreach ($documents as $document) {
                $document->reference()->delete();
            }
        }
    }

    public function findById(string $collection, string $id): bool
    {
        return $this->firestore->collection($collection)
                        ->document($id)
                        ->snapshot()
                        ->exists();
    }

    public function findByConditions($collection, array $conditions = []): bool
    {
        $query = $this->firestore->collection($collection);

        foreach ($conditions as $field => $value) {
            if ($field == 'id') {
                $field = '__name__';
            }

            $query = $query->where($field, '=', $value);
        }

        $documents = $query->limit(1)->documents();

        return !$documents->isEmpty();
    }

    public function countRows($collection, array $conditions = []): int
    {
        $query = $this->firestore->collection($collection);

        foreach ($conditions as $field => $value) {
            $query = $query->where($field, '=', $value);
        }

        return $query->documents()->size();
    }
}
