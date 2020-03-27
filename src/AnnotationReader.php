<?php
namespace Lynxcat\Annotation;

class AnnotationReader {

    /**
     * @var string annotation comment.
     */
    private $docComment;

    private $regx = "/@(\w+)(\(.*\)){1}/";

    private $annotations;

    private $annotationsParams;

    /**
     * set annotation comment
     * @param string $docComment
     * @return AnnotationReader
     */
    public function setDocComment(string $docComment): AnnotationReader
    {
        $this->docComment = $docComment;
        return $this;
    }

    /**
     * parse annotation comment
     * @return AnnotationReader
     */
    public function parse() : AnnotationReader
    {
        $result = [];
        preg_match_all($this->regx, $this->docComment, $result);

        $this->annotations = $result[1];

        if (!empty($this->annotations)){
            $this->annotationsParams = [];
            for ($i = 0, $len = count($this->annotations); $i < $len; $i++){
                $this->annotationsParams[$i] = $this->parseAnnotationParams($result[2][$i]);
            }
        }

        return $this;
    }

    /**
     * parse annotation params
     * @param $params
     * @return array
     */
    public function parseAnnotationParams($params){
        $params = trim($params, " \t\n\r \v()");

        $result = [];

        preg_match_all("/(\w+)=((\".*\")|(\{.*\}))/U", $params, $result);

        $len = count($result[0]);
        $res = [];

        if ($len == 0){
            $res["value"] = trim($params, "\"");
        }else{
            for ($i = 0; $i < $len; $i++){
                $res[$result[1][$i]] = $this->parseParamValue($result[2][$i]);
            }
        }

        return $res;
    }

    /**
     * parse annotation param value
     * @param $value
     * @return false|string|string[]
     */
    public function parseParamValue($value){
        if ($value[0] == "\""){
            $result = trim($value, "\"");
        }else{
            $result = explode(", ", str_replace(["\"", "{", "}"], "", $value));
        }

        return $result;
    }

    /**
     * get all annotation
     * @return array
     */
    public function getAnnotations(): array {
        $annotation = [];

        if ($this->annotations != null){
            $annotation = array_combine($this->annotations, $this->annotationsParams);
        }

        return $annotation;
    }
}
