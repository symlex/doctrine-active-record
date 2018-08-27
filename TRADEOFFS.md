# Tradeoffs

## Potential reasons to use Doctrine ActiveRecord

- The active record pattern is perfectly suited for building REST services (create, read, update, delete)
- It is a lot faster and less complex than Datamapper ORM implementations
- Small code footprint
- Easy to write unit tests (record & replay fixtures can automatically be created, see existing test suite) 
- Built on top of Doctrine DBAL
- Part of the Symlex framework stack for agile Web development

## Potential reasons not to use Doctrine ActiveRecord

- Doctrine ActiveRecord is not backed by a company and has a small community (you're welcome to join us)
- Development is mostly driven by a [single developer](https://blog.liquidbytes.net/about/) depending on the needs of 
  specific applications (development started to have a high-performance replacement for Doctrine ORM)
- Don't use it if you are not comfortable reading at least small amounts of library code (you're welcome to ask for 
  help via email or send additional docs as pull request)