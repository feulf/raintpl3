Rain TPL 3:
- removed static cache
- register_tag: example {@message@}
- syntax compatible with Rain Tpl 2
- loop improved: {loop="$name" as $key => $value}
- cascade modifier: {$title|substr:3,6|strlen}
- modifier in if: {if="$title|strlen > 10"}