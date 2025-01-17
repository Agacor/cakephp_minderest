<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Table\AppTable;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Productos Model
 *
 * @property \App\Model\Table\ClientesTable&\Cake\ORM\Association\BelongsToMany $Clientes
 *
 * @method \App\Model\Entity\Producto newEmptyEntity()
 * @method \App\Model\Entity\Producto newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Producto[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Producto get($primaryKey, $options = [])
 * @method \App\Model\Entity\Producto findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Producto patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Producto[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Producto|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Producto saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Producto[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Producto[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Producto[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Producto[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductosTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('productos');
        $this->setDisplayField('display');

        // [ORM]
        $this->hasMany('ProductosClientes', [
            'propertyName' => 'ProductosClientes',
            'className' => 'ProductosClientes',
            'foreignKey' => 'producto_id',
        ]);

        $this->belongsToMany('Clientes', [
            'foreignKey' => 'producto_id',
            'targetForeignKey' => 'cliente_id',
            'joinTable' => 'productos_clientes',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('mpn')
            ->maxLength('mpn', 255)
            ->requirePresence('mpn', 'create')
            ->notEmptyString('mpn')
            ->add('mpn', 'unique', [
                'rule' => 'validateUnique', 
                'provider' => 'table',
                'message' => __('El Manufacturer Part Number debe ser único.'),
            ]);

        $validator
            ->scalar('nombre')
            ->maxLength('nombre', 255)
            ->requirePresence('nombre', 'create')
            ->notEmptyString('nombre');

        $validator
            ->scalar('descripcion')
            ->maxLength('descripcion', 16777215)
            ->allowEmptyString('descripcion');

        $validator
            ->scalar('ean13')
            ->maxLength('ean13', 255)
            ->allowEmptyString('ean13');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['mpn'], __('El Manufacturer Part Number debe ser único.')));

        return $rules;
    }


    /******************************
     * CUSTOM FINDERS
     ******************************/
    
    /**
     * Custom Finder for Autocomplete
     * @param \Cake\ORM\Query $query
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function findAutocomplete(\Cake\ORM\Query $query, array $options)
    {
        $alias = $this->getAlias();
        $query->select([
            $alias."__id" => "$alias.id",
            $alias."__label" => "CONCAT('[', $alias.mpn, '] ', $alias.nombre)",
            $alias."__value" => "CONCAT('[', $alias.mpn, '] ', $alias.nombre)",
            $alias."__mpn" => "$alias.mpn",
            $alias."__nombre" => "$alias.nombre",
        ]);
        // Término de Búsqueda
        if (!empty($options['search'])) {
            $conditions = [];
            $search_words = explode(' ', $options['search']);
            foreach($search_words as $word) {
                $conditions[]=["CONCAT('[', $alias.mpn, '] ', $alias.nombre) LIKE" => "%$word%"];
            }
            $query->where($conditions);
        }
        return $query;
    }
}
