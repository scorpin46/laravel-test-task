<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Exception;
use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;
use Log;
use Str;
use Validator;

/**
 * Base model
 *
 * @mixin Eloquent
 * @mixin BaseEloquentBuilder
 * @mixin QueryBuilder
 */
abstract class BaseModel extends Model
{
    /**
     * @var string
     */
    const SCENARIO_DEFAULT = 'default';

    /**
     * @var bool
     */
    protected static $scenariosRequired = true;

    /**
     * Use "true" value for OFF auto validation before save (in your model or in object (objectModel::unguard(true))
     * Use "false" value for auto validation before save, for using fill method
     *
     * {@inheritDoc}
     */
    protected static $unguarded = false;

    /**
     * @var array
     */
    protected static $observers = [];

    /**
     * @var array
     */
    protected static $autoHash = [];

    /**
     * @var bool
     */
    public $wasRecentlyDeleted = false;

    /**
     * @var MessageBag
     */
    protected $errors;

    /**
     * @var MessageBag
     */
    protected $infoMessages;

    /**
     * {@inheritDoc}
     */
    protected $observables = [
        'validating',
        'validated',
        'loaded',
    ];

    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @var string
     */
    protected $scenario = self::SCENARIO_DEFAULT;

    /**
     * Validation scenarios
     *
     * @var array
     */
    protected $scenarios = [];

    /**
     * @var bool
     */
    protected $receiving = false;

    /**
     * @var bool
     */
    protected $fireEvents = true;

    /**
     * @var null|\Illuminate\Contracts\Events\Dispatcher
     */
    protected $eventDispatcher = null;

    /**
     * @var array
     */
    protected $tempAttributes = [];

    /**
     * @var array
     */
    protected $transformedToNullEmptyJsonAttrs = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        if (static::$scenariosRequired && static::class !== BaseModel::class) {
            $this->setScenario($this->scenario);
        }

        parent::__construct($attributes);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return with(new static)->getTable();
    }

    /**
     * @return array
     */
    protected static function getObservers()
    {
        return static::$observers ?? [];
    }

    /**
     * {@inheritDoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function(self $model) {
            if ( ! static::$unguarded && ! $model->validate()) {
                return false;
            }
        });

        foreach (static::getObservers() as $priority => $observerClass) {
            static::observe(new $observerClass);
        }

        static::saving(function(self $model) {
            $model->setNullForEmptyJsonAttrs();
        });
    }

    /**
     * Unescaped unicode chars
     *
     * @param mixed $value
     *
     * @return false|string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING);
    }

    /**
     *  Get validation rules
     */
    public function getRules()
    {
        $rules = $this->rules;

        if ($this->id) {
            foreach ($rules as $attr => $rule) {
                $thisTable    = $this->getTable();
                $rules[$attr] = preg_replace("/(unique:$thisTable)/", "$1,$attr,$this->id,id", $rule);
            }
        }

        return $rules;
    }

    /**
     * Get validation rule for attribute
     *
     * @param string $attribute
     *
     * @return string
     */
    public function getRule(string $attribute)
    {
        return Arr::get($this->getRules(), $attribute, '');
    }

    /**
     * Set errors of model
     *
     * @param MessageBag $messageBag
     *
     * @return $this
     */
    public function setErrors(MessageBag $messageBag)
    {
        $this->errors = $messageBag;

        return $this;
    }

    /**
     * Set info messages of model
     *
     * @param MessageBag $messageBag
     *
     * @return $this
     */
    public function setInfoMessages(MessageBag $messageBag)
    {
        $this->infoMessages = $messageBag;

        return $this;
    }

    /**
     * Get errors of model
     *
     * @return MessageBag
     */
    public function getErrors()
    {
        if ( ! isset($this->errors)) {
            $this->setErrors(new MessageBag());
        }

        return $this->errors;
    }

    /**
     * Get first error for attribute
     *
     * @param $attribute
     *
     * @return string
     */
    public function getError($attribute)
    {
        return $this->getErrors()->first($attribute);
    }

    /**
     * Get info messages of model
     *
     * @return MessageBag
     */
    public function getInfoMessages()
    {
        if ( ! isset($this->infoMessages)) {
            $this->setInfoMessages(new MessageBag());
        }

        return $this->infoMessages;
    }

    /**
     * Check model on errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return ! $this->getErrors()->isEmpty();
    }

    /**
     * Check model on valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->validate();
    }

    /**
     * Check model on invalid
     *
     * @return bool
     */
    public function isInvalid()
    {
        return ! $this->isValid();
    }

    /**
     * Check model on info messages
     *
     * @return bool
     */
    public function hasInfoMessages()
    {
        return ! $this->getInfoMessages()->isEmpty();
    }

    /**
     * @param string $key
     * @param string $message
     *
     * @return $this
     */
    public function addError($key, $message)
    {
        $this->getErrors()->add($key, $message);

        return $this;
    }

    /**
     * @param string $key
     * @param string $message
     *
     * @return $this
     */
    public function addInfoMessage($key, $message)
    {
        $this->getInfoMessages()->add($key, $message);

        return $this;
    }

    /**
     * @param string                         $keyPrefix
     * @param \Illuminate\Support\MessageBag $messageBag
     *
     * @return $this
     */
    public function addEmbedErrors(string $keyPrefix, MessageBag $messageBag)
    {
        $keyPrefix = trim($keyPrefix, '.');

        $errors = $this->getErrors();
        foreach ($messageBag->getMessages() as $key => $message) {
            $errors->add($keyPrefix . '.' . $key, $message);
        }

        return $this;
    }

    /**
     * Get validation rules for current validation scenario
     *
     * @return array
     */
    public function getScenarioRules()
    {
        if ( ! static::$scenariosRequired) {
            return $this->getRules();
        }

        $scenarioRules = [];

        foreach ($this->scenarios[$this->scenario] as $attribute) {
            $attributeIsNotRequired = Str::startsWith($attribute, '!');

            if ($attributeIsNotRequired) {
                $attribute = ltrim($attribute, '!');
            }

            if (array_key_exists($attribute, $this->getRules())) {
                $attributeRules = $this->getRules()[$attribute];

                if ($attributeIsNotRequired) {
                    $attributeRules = str_replace('required|', '', $attributeRules) . '|nullable';
                } else {
                    $attributeRules = 'required|' . $attributeRules;
                }

                $scenarioRules[$attribute] = trim($attributeRules, '|');
            }

            $subRules = array_filter($this->getRules(), function($key) use ($attribute) {
                return preg_match("/^$attribute\..+$/", $key);
            }, ARRAY_FILTER_USE_KEY);

            $scenarioRules = array_merge($scenarioRules, $subRules);
        }

        return $scenarioRules;
    }

    /**
     * Get validation rule for attribute of current validation scenario
     *
     * @param string $attribute
     *
     * @return string
     */
    public function getScenarioRule(string $attribute)
    {
        return Arr::get($this->getScenarioRules(), $attribute, '');
    }

    /**
     * Change scenario of validation
     *
     * @param string $scenario
     *
     * @return $this
     * @throws Exception
     */
    public function setScenario($scenario)
    {
        if ( ! is_string($scenario) || ! array_key_exists($scenario, $this->scenarios)) {
            throw new Exception(sprintf("Invalid validation scenario '%s' in model %s", $scenario, static::class));
        }

        $this->scenario = $scenario;
        $this->updateFillableByScenario($scenario);

        return $this;
    }

    /**
     * @return string
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Get fireEvents property
     *
     * @return bool
     */
    public function getFireEvents()
    {
        return $this->fireEvents;
    }

    /**
     * Update fillables attributes by scenario.
     *
     * @param string $scenario
     *
     * @return $this
     */
    private function updateFillableByScenario($scenario)
    {
        $fillable = [];

        foreach ($this->scenarios[$scenario] as $attribute) {
            $fillable[] = ltrim($attribute, '!');
        }

        $this->fillable($fillable);

        return $this;
    }

    /**
     * Validate model attributes
     *
     * @return bool
     */
    public function validate()
    {
        $attributes = $this->getAttributes();
        $rules      = $this->getScenarioRules();

        foreach ($attributes as $attribute => $value) {
            if ($value && in_array($attribute, $this->getDates())) {
                $attributes[$attribute] = $this->asDateTime($value);
            }
        }

        $validator = Validator::make($this->castAttributes($attributes), $rules);
        //$validator = Validator::make( $attributes, $rules);

        if ($validator->fails()) {
            $this->getErrors()->merge($validator->getMessageBag());

            Log::error('Validation errors', [
                'model'      => static::class,
                'errors'     => $this->getErrors()->getMessages(),
                'attributes' => $this->getAttributes(),
            ]);
        } else {
            $this->autoHash();
        }

        return ! $this->hasErrors();
    }

    /**
     * Устанавливает значение атрибута в модель.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getDates())) {
            try {
                $dateValue = $value
                    ? Carbon::parse($value)->toDateTimeString()
                    : null;

                parent::setAttribute($key, $dateValue);
            } catch (InvalidArgumentException $e) {
                $errorMessageParams = [
                    'attribute' => $key,
                    'format'    => 'Y-m-d',
                ];
                $this->addError($key, trans('validation.date_format', $errorMessageParams));
            }
        } else {
            //($this->isJsonCastable($key) || $this->isClassCastable($key) && && is_string($value))
            $this->isJsonCastable($key) && is_string($value)
                ? parent::setAttribute($key, json_decode($value)) //$this->fillJsonAttribute($key, $value)
                : parent::setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return array|mixed
     */
    public function getAttribute($key)
    {
        if (str_contains($key, '->')
            && ($parentKey = Str::before($key, '->'))
            && ($this->isJsonCastable($key) || $this->isJsonCastable($parentKey))
        ) {
            $dotKey = str_replace('->', '.', $key);

            return Arr::get(json_decode($this->toJson(), true), $dotKey);

            //$trueJson = json_encode($array);
            //return $this->hasCast($key)
            //    ? parent::castAttribute($key, $trueJson)
            //    : parent::castAttribute($parentKey, $trueJson);
        }

        return parent::getAttribute($key);
    }

    /**
     * Allow or disallow firing events. Set "false" to disallow firing events
     *
     * @param bool $fireEvents
     *
     * @return $this
     */
    public function setFireEvents(bool $fireEvents)
    {
        if ( ! $fireEvents) {
            $this->eventDispatcher = static::getEventDispatcher();
            static::unsetEventDispatcher();
        } else {
            static::setEventDispatcher($this->eventDispatcher);
            $this->eventDispatcher = null;
        }

        $this->fireEvents = $fireEvents;

        return $this;
    }

    /**
     * Cast attributes for model
     *
     * @param array $dirty
     *
     * @return array
     */
    private function castAttributes(array $dirty)
    {
        foreach ($dirty as $attribute => $value) {
            if ($this->hasCast($attribute)) {
                $dirty[$attribute] = $this->castAttribute($attribute, $value);
            }
        }

        return $dirty;
    }

    /**
     * Returns casted model attributes
     *
     * @return array
     */
    public function getCastsAttributes()
    {
        return $this->castAttributes($this->getAttributes());
    }

    /**
     * Регистрируем колбэк в качестве слушетеля
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function loaded($callback)
    {
        static::registerModelEvent('loaded', $callback);
    }

    /**
     * Перегружаем метод загрузки из базы
     *
     * @param array $attributes
     * @param null  $connection
     *
     * @return static|BaseModel
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        /** @var static $instance */
        $instance = parent::newFromBuilder($attributes, $connection);

        $instance->fireModelEvent('loaded', false);

        return $instance;
    }

    /**
     * Расширене кастинга атрибутов для возможности кастинга массивов.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if ( ! isset($this->casts[$key])) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $itemKey => &$itemValue) {
                $findKey   = $key . (! is_numeric($itemKey) ? ".$itemKey" : '');
                $itemValue = parent::castAttribute($findKey, $itemValue);
            }

            return $value;
        }

        return parent::castAttribute($key, $value);
    }

    /**
     * Return types of attributes
     *
     * @return array
     * */
    public function getCasts()
    {
        return $this->casts;
    }


    /**
     * Automatically hashes specified attributes before their saved to the database.
     *
     * @return void
     */
    public function autoHash()
    {
        foreach (static::$autoHash ?? [] as $attr) {
            $value = $this->attributes[$attr];
            if ($this->getOriginal($attr) != $value && mb_strlen($value) > 0 && Hash::needsRehash($value)) {
                $this->attributes[$attr] = Hash::make($value);
            }
        }
    }

    /**
     * @param $query
     *
     * @return \App\Models\BaseEloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new BaseEloquentBuilder($query);
    }

    /**
     * @param $attribute
     *
     * @return bool
     */
    public function hasTempAttribute($attribute)
    {
        return isset($this->tempAttributes[$attribute]);
    }

    /**
     * @param $attribute
     *
     * @return mixed|null
     */
    public function getTempAttribute($attribute)
    {
        return $this->tempAttributes[$attribute] ?? null;
    }

    /**
     * @return array|null
     */
    public function getTempAttributes()
    {
        return $this->tempAttributes ?? null;
    }

    /**
     * @param $attribute
     * @param $value
     */
    public function setTempAttribute($attribute, $value)
    {
        $this->tempAttributes[$attribute] = $value;
    }

    /**
     * @param $attribute
     */
    public function unsetTempAttribute($attribute)
    {
        unset($this->tempAttributes[$attribute]);
    }

    /**
     * @param $attributes
     */
    public function setTempAttributes($attributes)
    {
        foreach ($attributes as $key => $val) {
            $this->setTempAttribute($key, $val);
        }
    }

    /**
     * @param array|null $except
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function replicate(array $except = null)
    {
        $replicate = parent::replicate($except);
        $replicate->setTempAttributes($this->getTempAttributes());

        return $replicate;
    }

    /**
     * Set null where all params of json|class attr is empty
     *
     * @return $this
     */
    public function setNullForEmptyJsonAttrs()
    {
        collect($this->transformedToNullEmptyJsonAttrs)
            ->filter(fn($attr) => static::isJsonCastable($attr) || static::isClassCastable($attr))
            ->map(function ($attr){
                $value    = $this->getAttribute($attr);
                $arrValue = is_string($value)
                    ? json_decode($value, true)
                    : json_decode(json_encode($value), true);

                if ( ! $arrValue || ! array_filter(Arr::flatten($arrValue))){
                    $this->setAttribute($attr, null);
                }
            });

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributesKeys(): array
    {
        return array_keys($this->getAttributes());
    }
}
