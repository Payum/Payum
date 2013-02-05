<?php

namespace Payum\Domain\Storage;

use Payum\Domain\ModelInterface;

/**
 * Default storage. Do nothing.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class NullModelStorage implements ModelStorageInterface
{

    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        // Do nothing
      return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel(ModelInterface $model)
    {
      // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function findModelById($id)
    {
      // Do nothing
      return null;
    }
}
