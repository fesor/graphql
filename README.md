GraphQL for Symfony
=========================

![](https://scrutinizer-ci.com/g/fesor/graphql/badges/build.png?b=master)
![]()

This library provides you simple way to implement GraphQL API using it's 
[schema definition language](https://blog.graph.cool/graphql-sdl-schema-definition-language-6755bcb9ce51).

## Resolvers

GraphQL uses resolvers functions in order to fetch data. It acts as function, which receives some input
and returns value according to schema. 

```php
public function resolveUserProfile(array $args)
{
    ['id' => $id] = $args;
    
    return $this->db->fetchUser($id);
}
```

You could specify resolvers for any field in your schema. If resolver for some field is not provided, then default 
resolver will be used.

### Default Resolver

Default resolver is used to fetch single fields out of values, provided by resolvers higher in hierarchy. You could
treat it as leaf resolver in your tree.

It could retrieve values of fields from both arrays and objects. If object is given, and it has accessor for needed field,
then it would be called. You could also process arguments for given field.

For example, in parent resolver we returned user profile object, which should have `userPic` field. We also want to specify
size of picture in order to generate thumbnail. Then we could just add method `userPic($args)` or `getUserPic($args)` in our
object:

```php
class UserProfile
{
    // ...
    public function userPic(array $args): Thumb
    {
        ['width' => $width, 'height' => $height] = $args;
        return Thumb::forImage($this->userPic, $width, $height);
    }
}
```
