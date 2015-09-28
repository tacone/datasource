<?php

namespace Tacone\DataSource\Relation;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tacone\DataSource\DataSource;

class BelongsToManyWrapper extends AbstractWrapper
{
    public function saveAfter(Collection $children)
    {
        $result = [];
        foreach ($children as $child) {
            $result[] = DataSource::make($child)->save();
        }

        $this->relation->sync($children);
        return $result;
    }

    public function associate($key, Model $model)
    {
        // TODO: what to do here?

        /** @var Collection $collection */
        $collection = $this->relation->getParent()->$key;
        foreach ($collection as $position => $m) {
            // already in there
            if ($model === $m) {
                return;
            }

            if ($model->getKey() && $model->getKey() == $m->getKey()) {
                // found a different instance with the same key, substitute it
                $collection[$position] = $model;

                return;
            }
        }
        // if it get's to here, there's no matching model, so we just append the
        // new one at the end of the collection
        $collection[] = $model;
    }

    /**
     * @return Model
     */
    public function getChild()
    {
        return $this->relation->getRelated()->newCollection();
    }
}
