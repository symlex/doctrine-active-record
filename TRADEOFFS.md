# Should I use this library?

## Pros ##

  - The active record pattern is perfectly suited for building REST services (create, read, update, delete)
  - It is a lot faster and less complex than Datamapper ORM implementations
  - Small code footprint
  - Easy to write unit tests (record & replay fixtures can automatically be created, see existing test suite) 
  - Built on top of Doctrine DBAL
  - Part of the [Symlex](https://symlex.org/) framework stack for agile Web development

## Cons ##

  - While you can get [commercial support](https://blog.liquidbytes.net/contact/), 
    this library is not backed by a major company and has a small community
  - Don't use it if you are not comfortable reading at least small amounts of library code (you're welcome to ask for 
    help via email or send additional docs as pull request)