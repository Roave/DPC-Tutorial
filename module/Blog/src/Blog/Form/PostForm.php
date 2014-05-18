<?php
/**
 * User: garyhockin
 * Date: 05/05/2014
 * Time: 10:25
 */

namespace Blog\Form;


use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class PostForm extends Form
{

    /**
     * @var InputFilter
     */
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('post');

        /**
         * Hidden field for ID
         */
        $this->add(
            [
                'name' => 'id',
                'type' => 'hidden'
            ]
        );

        /**
         * Slug, in format lower-only-or-numbers-1234
         */
        $this->add(
            [
                'name' => 'slug',
                'type' => 'text',
                'options' => [
                    'label' => 'Slug'
                ],
                'attributes' => [
                    'class' => 'form-control'
                ],
            ]
        );

        /**
         * Date written on, should not be editable
         */
        $this->add(
            [
                'name' => 'written_on',
                'type' => 'text',
                'options' => [
                    'label' => 'Written On'
                ],
                'attributes' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
            ]
        );

        /**
         * Post title
         */
        $this->add(
            [
                'name' => 'title',
                'type' => 'text',
                'options' => [
                    'label' => 'Title'
                ],
                'attributes' => [
                    'class' => 'form-control'
                ],
            ]
        );

        /**
         * Preview text (first paragraph of copy usually)
         */
        $this->add(
            [
                'name' => 'preview',
                'type' => 'textarea',
                'options' => [
                    'label' => 'Preview'
                ],
                'attributes' => [
                    'class' => 'form-control'
                ],
            ]
        );

        /**
         * Body text of post
         */
        $this->add(
            [
                'name' => 'body',
                'type' => 'textarea',
                'options' => [
                    'label' => 'Body'
                ],
                'attributes' => [
                    'class' => 'form-control',
                    'rows' => 20
                ],
            ]
        );

        /**
         * Number of views of this post (hidden and defaults to 0)
         */
        $this->add(
            [
                'name' => 'views',
                'type' => 'hidden',
                'value' => '0',
            ]
        );

        /**
         * Submit button
         */
        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'class' => 'btn btn-default margin-top'
                ],
            ]
        );

    }

    /**
     * Defines the filtering and validation rules for the form fields set above
     *
     * @return null|InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add(
                [
                    'name' => 'slug',
                    'required' => 'true',
                    'filters' => [
                        ['name' => 'StringTrim'],
                    ],
                    'validators' => [
                        [
                            'name' => 'Regex',
                            'options' => [
                                'pattern' => '/^[a-z0-9-]+$/'
                            ]
                        ],
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'min' => 3,
                                'max' => 64
                            ]
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name' => 'title',
                    'required' => true,
                    'filters' => [
                        [
                            'name' => 'StringTrim',
                            'name' => 'StripTags',
                        ]
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'min' => 3,
                                'max' => 64
                            ]
                        ]
                    ]
                ]
            );

            $this->inputFilter->add(
                [
                    'name' => 'views',
                    'required' => false
                ]
            );

            $this->inputFilter->add(
                [
                    'name' => 'preview',
                    'required' => true,
                ]
            );

            $this->inputFilter->add(
                [
                    'name' => 'body',
                    'required' => true,
                ]
            );
        }

        return $this->inputFilter;
    }

} 