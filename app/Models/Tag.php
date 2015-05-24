<?php

namespace FireflyIII\Models;

use App;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

/**
 * Class Tag
 *
 * @package FireflyIII\Models
 */
class Tag extends Model
{
    use ValidatingTrait;

    protected $fillable = ['user_id', 'tag', 'date', 'description', 'longitude', 'latitude', 'zoomLevel', 'tagMode'];
    protected $rules
                        = [
            'tag'         => 'required|min:1|uniqueObjectForUser:tags,tag,TRUE',
            'description' => 'min:1',
            'date'        => 'date',
            'latitude'    => 'numeric|min:-90|max:90',
            'longitude'   => 'numeric|min:-90|max:90',
            'tagMode'     => 'required|in:nothing,balancingAct,advancePayment'
        ];

    /**
     * @param array $fields
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return Tag|null
     */
    public static function firstOrCreateEncrypted(array $fields)
    {
        // everything but the tag:
        if (isset($fields['tagMode'])) {
            unset($fields['tagMode']);
        }
        $query = Tag::orderBy('id');
        foreach ($fields as $name => $value) {
            if ($name != 'tag') {
                $query->where($name, $value);
            }
        }
        $set = $query->get(['tags.*']);
        /** @var Tag $tag */
        foreach ($set as $tag) {
            if ($tag->tag == $fields['tag']) {
                return $tag;
            }
        }
        // create it!
        $fields['tagMode']     = 'nothing';
        $fields['description'] = isset($fields['description']) && !is_null($fields['description']) ? $fields['description'] : '';
        $tag                   = Tag::create($fields);

        return $tag;

    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'date'];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     *
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        return Crypt::decrypt($value);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     *
     * @return string
     */
    public function getTagAttribute($value)
    {
        return Crypt::decrypt($value);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Crypt::encrypt($value);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     */
    public function setTagAttribute($value)
    {
        $this->attributes['tag'] = Crypt::encrypt($value);
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactionjournals()
    {
        return $this->belongsToMany('FireflyIII\Models\TransactionJournal');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('FireflyIII\User');
    }
}
