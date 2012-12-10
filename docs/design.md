# Design of Personalisation Module

## Principles

The module is built to a set of principles:
 *	Core components are identified, and each has an interface to support multiple implementations.
 *	Wherever possible, data and state is provisioned on-demand to minimise overhead.
 *	The set of state that is known about the user is derived from each request.

## Concepts

 *	Each request can carry one or more pieces of state that are used by a **tracking identity finder**
	(TrackingIdentityFinder implementor) in order to understand the **identity** (TrackingIdentity) of the user.
 *	A **tracker** (Tracker) manages state recorded against an identity using one or more **tracking stores**
	(TrackingStore implementers).
 *	When a personalised spot needs to be derived, a **selection provider** (SelectionProvider implementer) is invoked
	to determine the output, which is typically an identifier for an **output variation**.
 *	The selection provider may have arbitrary rules or business logic to make it's output decision.
 *	The selection provider is given a **context provider** which it uses to query what is known about the user.
 *	The context provider in turn derives the data from one or more handlers.
 *	Handlers exists that lets the context provider retrieve data from: the tracker; the raw request; the Member. Custom
  	handlers can also be provided.
 *	If the output of the selection provider identifies an output variation, then the variation is rendered, and returned
 	to the initiating context (e.g. Page where personalised item will appear).

## Representation of Tracking State

Data that is known about a user is represented in a consistent and canonical way. The context provider is the aggregation
point for all data for a given request, which includes the request itself, database state and tracking state for the
identified user. The tracker and tracking stores represent the subset they store in the same way that the context
provider does.

The context provider state is a key/value store, with the following characteristics:

 *	Keys are namespaced. e.g. a key "request.referrer" might talk about a referrer URL in the context of the request,
 	whereas "profile.wealthy_rural" might talk about a user characteristic that has been derived from behaviour.
 *	Property names and their namespaces are defined by convention and default implementation.
 *	Metadata is known about each key:
 	*	It's data type
 	*	Whether the key supports multiple instances or not
 *	The value or values associated with a key conform to the data type.
 *	Each value carries additional metadata, such as a timestamp on which it was created.

The getProperties method is used to retrieve property values. It is provided a list of properties to retrieve. By allowing
multiple properties to be fetched at once will reduce latency if the store is external. Each property that is
requested will by default return a single value. If the property has multiple values, the latest is returned. This
can be controlled by passing additional info about the property, so that multiple values could be returned.

(@todo filtering should be considered for multiple values; this could be on the whole request, or on specific
properties. Filter implementation will be tracking store dependent. These might be names that identify pre-built filters,
or might be implementation-independent descriptions of the query.)

metadata
 *	Basic list
 *	Query usage of the keys across identities

Implementation notes:
 *	Some parts of the context provider's known state is singular, such as the request, and is derived dynamically.
 *	The tracking store can typically record history, so it needs to support multiple property values. Because of
 	the large number of values that might be generated for a property over time, a single database object should not
 	try to represent all values.
 *	Tracking stores may exist outside of SilverStripe.

## Unresolved Issues

 *	ContextProvider and Tracker perform some of the same tasks. Can roles be refactored?
 *	SelectionProvider can provide varying outputs: variation identification or actual result, which leads to an
 	inconsistent model.