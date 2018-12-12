<?php
/**
 * CakeManager (http://cakemanager.org)
 * Copyright (c) http://cakemanager.org
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) http://cakemanager.org
 * @link          http://cakemanager.org CakeManager Project
 * @since         1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Analyzer\Model\Table;

use Analyzer\Model\Entity\Visitor;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Visitors Model
 *
 * @property \Cake\ORM\Association\HasMany $Requests
 *
 * @method Visitor get($primaryKey, $options = [])
 * @method Visitor newEntity($data = null, array $options = [])
 * @method Visitor[] newEntities(array $data, array $options = [])
 * @method Visitor|bool save(EntityInterface $entity, $options = [])
 * @method Visitor patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Visitor[] patchEntities($entities, array $data, array $options = [])
 * @method Visitor findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class VisitorsTable extends Table
{

    /**
     * set connection name
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        $connection = Configure::read('Analyzer.connection');
        if (!empty($connection)) {
            return $connection;
        }

        return parent::defaultConnectionName();
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     *
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('analyzer_visitors');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Requests', [
            'foreignKey' => 'visitor_id',
            'className'  => 'Analyzer.Requests',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     *
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('client_ip');

        return $validator;
    }
}
