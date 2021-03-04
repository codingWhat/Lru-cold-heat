<?php

//Lru实现冷热数据

class Node {
    public $val;
    public $key;
    public $next;
    public $prev;
    public $time;
    public $isHot;

    public function __construct($key, $val)
    {
        $this->key = $key;
        $this->val = $val;
        $this->next = null;
        $this->prev = null;
        $this->time = time();
        $this->isHot = false;
    }
}

class Lru {
    public $head;
    public $tail;
    public $coldHead;
    public $cap;
    public $container;
    public $size;

    public function __construct($cap = 10)
    {
        $this->head = new Node("head", -1);
        $this->tail = new Node("tail", -2);
        $this->container = [];
        $this->cap = $cap;
        $this->size = 0;
    }

    public function put($key, $val)
    {
        $newNode = new Node($key, $val);
        if (isset($this->container[$key])) {
            $node = $this->container[$key];
            // 删除旧元素，挪到链表的头节点
            $this->removeNode($node);
        } else {
            //判断是否超出大小, 若超出移除旧原素，添加新元素即可
            if ($this->size + 1 > $this->cap) {
                $lastKey = $this->removeLastColdNode();
                $this->size--;
                unset($this->container[$lastKey]);
            }
        }
        $this->size++;
        $this->moveToColdHead($newNode);
        $this->container[$key] = $newNode;
    }

    public function removeLastColdNode()
    {
        if ($this->tail->prev) {
            $tmpNode = $this->tail->prev;
            if ($this->coldHead == $this->tail->prev) {
                $this->coldHead = $this->tail;
            }
             $tmpNode->prev->next = $this->tail;
             $this->tail->prev = $tmpNode->prev;

            $tmpNode->prev = null;
            $tmpNode->next = null;
            return  $tmpNode->key;
        }
    }



    public function get($key)
    {
        if (isset($this->container[$key])) {
            //判断是否被访问过，是否超过1s,如果超过的话，将节点移至热区域。
            $node = $this->container[$key];
            $currTime = time() + 2; //控制热数据的条件
            if (!$node->isHot && ($currTime - $node->time) > 1) {
                //更新冷数据头指针
                if ($this->coldHead == $node) {
                    $this->coldHead = $this->coldHead->next;
                }

                //移除节点
                $node->next->prev = $node->prev;
                $node->prev->next =  $node->next;

                //将节点挪到热数据区域
                $node->prev = $this->head;
                $node->next = $this->head->next;
                $this->head->next->prev = $node;
                $this->head->next = $node;
                $node->isHot = true;

            }

            return $this->container[$key];
        } else {
            return -1;
        }
    }

    function moveToColdHead ($node) {
        if (!$this->head->next && !$this->tail->prev && !$this->coldHead) {
            $this->head->next = $node;
            $this->tail->prev = $node;
            $node->next = $this->tail;
            $node->prev = $this->head;

            $this->coldHead = $node;
            return;
        }

        if (!$this->coldHead) {
            throw new Exception("coldHead 为 null!");
        }

        $node->time = time();
        $node->isAccessed = false;
        $node->next = $this->coldHead;
        $node->prev = $this->coldHead->prev;

        $this->coldHead->prev->next = $node;
        $this->coldHead->prev = $node;

        $this->coldHead = $node;

        return;
    }



    function removeNode($node) {
        $node->prev->next = $node->next;
        $node->next->prev = $node->prev;

        $node->prev = null;
        $node->next = null;
    }

    public function printCurrentInfo()
    {
        $cur = $this->head->next;

        while ($cur->next){
            echo "curr, key:{$cur->key}, val:{$cur->val}, coldHead->key:{$this->coldHead->key}, coldHead->val:{$this->coldHead->val}, " . PHP_EOL;
            $cur = $cur->next;
        }

    }

}

$lru = new Lru(3);

//$lru->put("a", 1);
//$lru->put("b", 2);
//$lru->get("a");
//
//$lru->put("c", 3);
//$lru->get("c");
//$lru->put("d", 4);
//$lru->get("d");
//$lru->put("e", 5);
//$lru->get("e");
//$lru->put("f", 6);
//$lru->get("f");

$lru->put("a", 1);
$lru->get("a");
$lru->put("b", 2);
$lru->put("c", 3);
$lru->put("d", 4);
//var_dump($lru->coldHead->val, $lru->coldHead->next->val);

$lru->printCurrentInfo();