<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: HMS/RealData.proto

namespace Hoymiles;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>symcon.hoymiles.InverterState</code>
 */
class InverterState extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int64 sn = 1;</code>
     */
    protected $sn = 0;
    /**
     * Generated from protobuf field <code>int32 ver = 2;</code>
     */
    protected $ver = 0;
    /**
     * Generated from protobuf field <code>int32 v = 3;</code>
     */
    protected $v = 0;
    /**
     * Generated from protobuf field <code>int32 freq = 4;</code>
     */
    protected $freq = 0;
    /**
     * Generated from protobuf field <code>int32 p = 5;</code>
     */
    protected $p = 0;
    /**
     * Generated from protobuf field <code>int32 q = 6;</code>
     */
    protected $q = 0;
    /**
     * Generated from protobuf field <code>int32 i = 7;</code>
     */
    protected $i = 0;
    /**
     * Generated from protobuf field <code>int32 pf = 8;</code>
     */
    protected $pf = 0;
    /**
     * Generated from protobuf field <code>int32 temp = 9;</code>
     */
    protected $temp = 0;
    /**
     * Generated from protobuf field <code>int32 wnum = 10;</code>
     */
    protected $wnum = 0;
    /**
     * Generated from protobuf field <code>int32 crc = 11;</code>
     */
    protected $crc = 0;
    /**
     * Generated from protobuf field <code>int32 link = 12;</code>
     */
    protected $link = 0;
    /**
     * Generated from protobuf field <code>int32 p_lim = 13;</code>
     */
    protected $p_lim = 0;
    /**
     * Generated from protobuf field <code>int32 mi_signal = 20;</code>
     */
    protected $mi_signal = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $sn
     *     @type int $ver
     *     @type int $v
     *     @type int $freq
     *     @type int $p
     *     @type int $q
     *     @type int $i
     *     @type int $pf
     *     @type int $temp
     *     @type int $wnum
     *     @type int $crc
     *     @type int $link
     *     @type int $p_lim
     *     @type int $mi_signal
     * }
     */
    public function __construct($data = NULL) {
        \Hoymiles\RealData::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int64 sn = 1;</code>
     * @return int|string
     */
    public function getSn()
    {
        return $this->sn;
    }

    /**
     * Generated from protobuf field <code>int64 sn = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setSn($var)
    {
        GPBUtil::checkInt64($var);
        $this->sn = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 ver = 2;</code>
     * @return int
     */
    public function getVer()
    {
        return $this->ver;
    }

    /**
     * Generated from protobuf field <code>int32 ver = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setVer($var)
    {
        GPBUtil::checkInt32($var);
        $this->ver = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 v = 3;</code>
     * @return int
     */
    public function getV()
    {
        return $this->v;
    }

    /**
     * Generated from protobuf field <code>int32 v = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setV($var)
    {
        GPBUtil::checkInt32($var);
        $this->v = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 freq = 4;</code>
     * @return int
     */
    public function getFreq()
    {
        return $this->freq;
    }

    /**
     * Generated from protobuf field <code>int32 freq = 4;</code>
     * @param int $var
     * @return $this
     */
    public function setFreq($var)
    {
        GPBUtil::checkInt32($var);
        $this->freq = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 p = 5;</code>
     * @return int
     */
    public function getP()
    {
        return $this->p;
    }

    /**
     * Generated from protobuf field <code>int32 p = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setP($var)
    {
        GPBUtil::checkInt32($var);
        $this->p = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 q = 6;</code>
     * @return int
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * Generated from protobuf field <code>int32 q = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setQ($var)
    {
        GPBUtil::checkInt32($var);
        $this->q = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 i = 7;</code>
     * @return int
     */
    public function getI()
    {
        return $this->i;
    }

    /**
     * Generated from protobuf field <code>int32 i = 7;</code>
     * @param int $var
     * @return $this
     */
    public function setI($var)
    {
        GPBUtil::checkInt32($var);
        $this->i = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 pf = 8;</code>
     * @return int
     */
    public function getPf()
    {
        return $this->pf;
    }

    /**
     * Generated from protobuf field <code>int32 pf = 8;</code>
     * @param int $var
     * @return $this
     */
    public function setPf($var)
    {
        GPBUtil::checkInt32($var);
        $this->pf = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 temp = 9;</code>
     * @return int
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Generated from protobuf field <code>int32 temp = 9;</code>
     * @param int $var
     * @return $this
     */
    public function setTemp($var)
    {
        GPBUtil::checkInt32($var);
        $this->temp = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 wnum = 10;</code>
     * @return int
     */
    public function getWnum()
    {
        return $this->wnum;
    }

    /**
     * Generated from protobuf field <code>int32 wnum = 10;</code>
     * @param int $var
     * @return $this
     */
    public function setWnum($var)
    {
        GPBUtil::checkInt32($var);
        $this->wnum = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 crc = 11;</code>
     * @return int
     */
    public function getCrc()
    {
        return $this->crc;
    }

    /**
     * Generated from protobuf field <code>int32 crc = 11;</code>
     * @param int $var
     * @return $this
     */
    public function setCrc($var)
    {
        GPBUtil::checkInt32($var);
        $this->crc = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 link = 12;</code>
     * @return int
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Generated from protobuf field <code>int32 link = 12;</code>
     * @param int $var
     * @return $this
     */
    public function setLink($var)
    {
        GPBUtil::checkInt32($var);
        $this->link = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 p_lim = 13;</code>
     * @return int
     */
    public function getPLim()
    {
        return $this->p_lim;
    }

    /**
     * Generated from protobuf field <code>int32 p_lim = 13;</code>
     * @param int $var
     * @return $this
     */
    public function setPLim($var)
    {
        GPBUtil::checkInt32($var);
        $this->p_lim = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 mi_signal = 20;</code>
     * @return int
     */
    public function getMiSignal()
    {
        return $this->mi_signal;
    }

    /**
     * Generated from protobuf field <code>int32 mi_signal = 20;</code>
     * @param int $var
     * @return $this
     */
    public function setMiSignal($var)
    {
        GPBUtil::checkInt32($var);
        $this->mi_signal = $var;

        return $this;
    }

}
