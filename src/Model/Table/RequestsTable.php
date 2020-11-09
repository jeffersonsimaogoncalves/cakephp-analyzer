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

use Analyzer\Model\Entity\Request;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use DateTime;

/**
 * Requests Model
 *
 * @property BelongsTo $Visitors
 *
 * @method Request get($primaryKey, $options = [])
 * @method Request newEntity($data = null, array $options = [])
 * @method Request[] newEntities(array $data, array $options = [])
 * @method Request|bool save(EntityInterface $entity, $options = [])
 * @method Request patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Request[] patchEntities($entities, array $data, array $options = [])
 * @method Request findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RequestsTable extends Table
{

    /**
     * set connection name
     *
     * @return string
     */
    public static function defaultConnectionName(): string
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
     * @param  array  $config  The configuration for the Table.
     *
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('analyzer_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Visitors', [
            'foreignKey' => 'visitor_id',
            'className' => 'Analyzer.Visitors',
        ]);
    }

    public function findUniqueVisitors(Query $query, array $options)
    {
        $query->group('Requests.visitor_id');

        return $query;
    }

    public function findBetween(Query $query, array $options)
    {
        if (array_key_exists('start', $options)) {
            $query->where([
                'Requests.created <=' => new DateTime($options['start']),
            ]);
        }

        if (array_key_exists('end', $options)) {
            $query->where([
                'Requests.created >=' => new DateTime($options['end']),
            ]);
        }

        return $query;
    }

    /**
     * Default validation rules.
     *
     * @param  \Cake\Validation\Validator  $validator  Validator instance.
     *
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmptyString('id');

        $validator
            ->allowEmptyString('url');

        $validator
            ->allowEmptyString('plugin');

        $validator
            ->allowEmptyString('controller');

        $validator
            ->allowEmptyString('action');

        $validator
            ->allowEmptyString('ext');

        $validator
            ->allowEmptyString('prefix');

        $validator
            ->allowEmptyString('pass');

        $validator
            ->allowEmptyString('query');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param  \Cake\ORM\RulesChecker  $rules  The rules object to be modified.
     *
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['visitor_id'], 'Visitors'));

        return $rules;
    }
}
